#!/usr/bin/ruby

ENV['GEM_PATH'] = '/home/singpolyma/.gems:/usr/lib/ruby/gems/1.8'

require 'rubygems'
require 'hpricot'
require 'uri'
require 'mysql'
require 'time'
require 'lib/representative_hcard'
require 'lib/get_doc'
require 'lib/hcard_urls'

# db format is:
# hostname username password database
db_settings = open('/home/singpolyma/buddydb').read.split(/\s+/)

uri = URI.parse(ARGV[0]).normalize
doc = get_doc(uri)

if doc.nil?
	$stderr.puts "Fetch error for #{uri.to_s}, removing from queue"
	db = Mysql.new(db_settings[0],db_settings[1],db_settings[2],db_settings[3])
	db.real_query("SET NAMES 'UTF8'")
	db.real_query("DELETE FROM queue WHERE url='#{Mysql.quote(ARGV[0])}'")
	db.close
	exit
end

hcard = representative_hcard(doc, uri.to_s)

given_name = ''
family_name = ''
additional_name = ''
bday = 0
tz = ''
fn = ''
urls = []
contacts = {}
nicknames = []
photos = []
emails = []
ims = []

if hcard
	given_name = hcard.at('.n .given-name').inner_html.gsub(/<.*?>/,'').strip rescue ''
	family_name = hcard.at('.n .family-name').inner_html.gsub(/<.*?>/,'').strip rescue ''
	additional_name = hcard.at('.n .additional-name').inner_html.gsub(/<.*?>/,'').strip rescue ''
	if given_name == ''
		given_name = hcard.at('.fn').inner_html.gsub(/<.*?>/,'').strip.gsub(/&amp;/,'&').split(/ /) rescue []
		if given_name.size > 1 && given_name[1] != '&' 
			family_name = given_name.reverse[0]
			given_name = given_name[0]
		else
			nicknames.push given_name.join(' ')
			given_name = ''
		end
	end
	bday = hcard.at('.bday')
	unless bday.nil?
		if bday.name == 'abbr'
			bday = bday.attributes['title']
		else
			bday = bday.inner_html.gsub(/<.*?>/,'').strip
		end
		bday = Time.parse(bday).utc.to_i
	end
	tz = hcard.at('.tz').inner_html.gsub(/<.*?>/,'').strip rescue ''
	hcard.search('.nickname').each do |nick|
		nicknames.push nick.inner_html.gsub(/<.*?>/,'').strip.gsub(/&amp;/,'&')
	end
	hcard.search('.photo').each do |photo|
		if photo.name == 'img'
			photos.push photo.attributes['src']
		elsif photo.name == 'a'
			photos.push photo.attributes['href']
		else
			photos.push photo.inner_html.gsub(/<.*?>/,'').strip.gsub(/&amp;/,'&')
		end
	end
	#photo at h2 #profile-image is a Twitter oddity
	begin
		photos.push doc.at('h2 img#profile-image').attributes['src'] if uri.to_s =~ /twitter\.com/
	rescue
	end
	hcard.search('.email') do |email|
		if email.attributes['href']
			emails.push email.attributes['href'].scan(/^mailto:(.*)$/)[0][0]
		else
			emails.push email.inner_html.gsub(/<.*?>/,'').strip.gsub(/&amp;/,'&')
		end
	end
end

if given_name != '' && family_name != ''
	fn = given_name + ' ' + family_name
end
if given_name != '' && family_name == ''
	fn = given_name
end
if nicknames.size > 0 && given_name == ''
	fn = nicknames[0]
end
if nicknames.size < 1 && given_name == '' && family_name != ''
	fn = 'The ' + family_name
end
if hcard && hcard.at('.fn')
	begin
		fn = hcard.at('.fn').inner_html.gsub(/<.*?>/,'').strip.gsub(/&amp;/,'&')
	rescue
	end
end

urls, contacts = hcard_urls(hcard, doc, uri)

urls.each do |url|
	if url =~ /^mailto:/
		emails.push url.scan(/^mailto:(.*)$/)[0][0]
		urls.delete url
	end
	if url =~ /^xmpp:/ or url =~ /^sip:/ or url =~ /^aim:goim\?screenname=/ or url =~ /^ymsgr:sendIM\?/ or url =~ /^msnim:chat\?contact=/
		ims.push url
		urls.delete url
	end
	if url =~ /^gtalk:chat\?jid=/
		emails.push 'xmpp:' + url.scan(/^gtalk:chat\?jid=(.*)$/)[0][0]
		urls.delete url
	end
	if url =~ /^gtalk:call\?jid=/
		emails.push 'xmpp:' + url.scan(/^gtalk:call\?jid=(.*)$/)[0][0]
		urls.delete url
	end
end

doc.search('a[@rel~=contact],a[@rel~=friend],a[@rel~=acquaintance],a[@rel~=met],a[@rel~=co-worker],a[@rel~=colleague],a[@rel~=co-resident],a[@rel~=neighbor],a[@rel~=child],a[@rel~=parent],a[@rel~=sibling],a[@rel~=spouse],a[@rel~=kin],a[@rel~=muse],a[@rel~=crush],a[@rel~=date],a[@rel~=sweetheart]') do |contact|
	if contact.parent.attributes['class'].to_s.split(/ /).index('vcard').nil?
		contact.set_attribute 'class', 'url'
		contact.parent.set_attribute 'class', 'vcard'
	end
	contacts[contact.attributes['href']] = {'rel' => contact.attributes['rel'].split(/ /), doc => contact.parent}
end

if uri.to_s =~ /facebook\.com/ #Facebok contact hacks
	doc.search('a').each do |contact|
		if contact.attributes['href'] =~ /facebook\.com\/people\/[^\/]+\/\d+/
			contacts[contact.attributes['href']] = {'rel' => 'friend', doc => nil}
		end
	end
end

db = Mysql.new(db_settings[0],db_settings[1],db_settings[2],db_settings[3])
db.real_query("SET NAMES 'UTF8'")

#db.real_query("INSERT INTO queue (url, next_update) VALUES ('#{Mysql.quote(uri.to_s)}', #{Time.now.utc.to_i + 300000}) ON DUPLICATE KEY UPDATE next_update=#{Time.now.utc.to_i + 300000}")
		#queue hacks
		db.real_query("DELETE FROM queue WHERE url='#{Mysql.quote(ARGV[0])}'")
		db.real_query("DELETE FROM queue WHERE url='#{Mysql.quote(uri.to_s)}'")

res = db.query("SELECT * FROM urls WHERE url='#{Mysql.quote(uri.to_s)}' LIMIT 1")
url_row = res.fetch_hash
res.free

if url_row.nil?
	res = db.query("SELECT * FROM urls WHERE verified=1 AND url IN ('#{urls.join('\',\'')}')")
	url_row = res.fetch_hash
	res.free
	if url_row.nil?
		db.real_query("INSERT INTO people (fn, `given-name`, `family-name`, `additional-name`, bday, tz) VALUES ('#{Mysql.quote(fn.to_s)}','#{Mysql.quote(given_name.to_s)}','#{Mysql.quote(family_name.to_s)}','#{Mysql.quote(additional_name.to_s)}',#{bday.to_i},'#{Mysql.quote(tz.to_s)}')")
		person_id = db.insert_id
		db.real_query("INSERT INTO urls (url, person_id, verified) VALUES ('#{Mysql.quote(uri.to_s)}', #{person_id}, 1)")
	else
	# This is false data - if we mark it unverified, we may accidentally verify it later... just stop
	#	person_id = url_row['person_id']
	#	db.real_query("INSERT INTO urls (url, person_id, verified) VALUES ('#{Mysql.quote(uri.to_s)}', #{url_row['person_id']}, 0)")
p url_row
puts 'bad data'
		exit
	end
else
		person_id = url_row['person_id']
		urls.push uri.to_s #if this one has already been verified, that counts as being verified
		res = db.query("SELECT person_id FROM urls WHERE person_id=#{person_id} AND verified=1 AND url IN ('#{urls.join('\',\'')}') LIMIT 1")
		verified = res.fetch_hash
		res.free

		res = db.query("SELECT person_id,url FROM urls WHERE person_id!=#{person_id} AND verified=1 AND url IN ('#{urls.join('\',\'')}') LIMIT 1")
		dupe = res.fetch_hash
		res.free
		
		unless verified.nil?
			db.real_query("UPDATE urls SET verified=1 WHERE url='#{Mysql.quote(uri.to_s)}'")
			res = db.query("SELECT * FROM people WHERE person_id=#{person_id} LIMIT 1")
			person = res.fetch_hash
			res.free
			if dupe
				dupe_uri = URI.parse(dupe['url'])
				dupe_doc = get_doc(dupe_uri)
				dupe_hcard = representative_hcard(dupe_doc, dupe_uri.to_s)
				dupe_urls = hcard_urls(dupe_hcard, dupe_doc, dupe_uri)[0]
				iz_dupe = false
				dupe_urls.each do |url|
					iz_dupe = true unless urls.index(url).nil?
				end
				if iz_dupe
					db.real_query("UPDATE IGNORE urls SET person_id=#{dupe['person_id']} WHERE person_id=#{person_id}")
					db.real_query("UPDATE IGNORE organizations SET person_id=#{dupe['person_id']} WHERE person_id=#{person_id}")
					db.real_query("UPDATE IGNORE `fields` SET person_id=#{dupe['person_id']} WHERE person_id=#{person_id}")
					db.real_query("UPDATE IGNORE contacts SET person_id=#{dupe['person_id']} WHERE person_id=#{person_id}")
					db.real_query("UPDATE IGNORE categories SET person_id=#{dupe['person_id']} WHERE person_id=#{person_id}")
					db.real_query("UPDATE IGNORE addresses SET person_id=#{dupe['person_id']} WHERE person_id=#{person_id}")
					db.real_query("DELETE FROM people WHERE person_id=#{person_id}")
					person_id = dupe['person_id']
				end
			end
			sql = []
			if fn.to_s != '' && fn.to_s.length >= person['fn'].length
				sql.push "fn='#{Mysql.quote(fn.to_s)}'"
			end
			if given_name.to_s != '' && given_name.to_s.length >= person['given-name'].length
				sql.push "`given-name`='#{Mysql.quote(given_name.to_s)}'"
			end
			if family_name.to_s != '' && family_name.to_s.length >= person['family-name'].length
				sql.push "`family-name`='#{Mysql.quote(family_name.to_s)}'"
			end
			if additional_name.to_s != '' && additional_name.to_s.length >= person['additional-name'].length
				sql.push "`additional-name`='#{Mysql.quote(additional_name.to_s)}'"
			end
			if bday.to_i > 0
				sql.push "bday=#{bday.to_i}"
			end
			if tz.to_s != ''
				sql.push "tz='#{Mysql.quote(tz.to_s)}'"
			end
			db.real_query("UPDATE people SET #{sql.join(',')} WHERE person_id=#{url_row['person_id']}") if sql.size > 0
		else
			db.real_query("DELETE FROM urls WHERE url='#{Mysql.quote(uri.to_s)}'")
			exit ## This URL no longer belongs to anyone, die
		end
end

if uri.to_s =~ /facebook\.com/
	db.real_query("INSERT IGNORE INTO urls (url, person_id, verified) VALUES ('#{Mysql.quote('http://www.facebook.com/profile.php?id=' + uri.to_s.scan(/facebook\.com\/people\/[^\/]+\/(\d+)$/)[0][0])}', #{person_id}, 1)")
end

urls.delete(uri.to_s)
urls.each do |url|
	db.real_query("INSERT IGNORE INTO urls (url, person_id, verified) VALUES ('#{Mysql.quote(url)}', #{person_id}, 0)")
	db.real_query("INSERT IGNORE INTO queue (url) VALUES ('#{Mysql.quote(url)}')")
end

ims.each do |im|
	db.real_query("INSERT INTO urls (url, person_id, verified) VALUES ('#{Mysql.quote(im)}', #{person_id}, 2) ON DUPLICATE KEY UPDATE verified=2")
end

contacts.each do |url, data|
	db.real_query("INSERT IGNORE INTO contacts (person_id, url) VALUES (#{person_id}, '#{Mysql.quote(url)}')")
	db.real_query("INSERT IGNORE INTO queue (url) VALUES ('#{Mysql.quote(url)}')")
end

nicknames.each do |nick|
	db.real_query("INSERT IGNORE INTO fields (person_id,type,value) VALUES (#{person_id},'nickname','#{Mysql.quote(nick)}')")
end

photos.each do |photo|
	db.real_query("INSERT IGNORE INTO fields (person_id,type,value) VALUES (#{person_id},'photo','#{Mysql.quote(photo)}')")
end

emails.each do |email|
   t = CGI.unescape(CGI.unescapeHTML(email)).gsub(/\s*[^\w]at[^\w]?\s*/,'@').gsub(/\s*[^\w]dot[^\w]\s*/,'.').split(/\s+/)
	email = nil
	t.each do |v|
		email = v.split(/\?/)[0] if v =~ /@/
	end
	next if email.nil?
	db.real_query("INSERT IGNORE INTO fields (person_id,type,value) VALUES (#{person_id},'email','#{Mysql.quote(email)}')")
end

db.close
