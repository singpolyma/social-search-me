#!/usr/bin/ruby

ENV['GEM_PATH'] = '/home/singpolyma/.gems:/usr/lib/ruby/gems/1.8'

require 'rubygems'
require 'open-uri'
require 'hpricot'
require 'uri'
require 'json'
require 'mysql'
require 'time'
require 'iconv'

def representative_hcard(doc, url)

	uri = URI.parse(url).normalize
	url = uri.to_s

	cards = doc.search('.vcard')
	return cards[0] if cards.size == 1

	cards.each do |vcard|
		uid = vcard.at('.uid')
		unless uid.nil?
			begin
				if uid.attributes['href'].reverse[0..0] == '/' && url.reverse[0..0] != '/'
					iz = (uid.name == 'a' && uid.attributes['href'] == "#{url}/")
				else
					iz = (uid.name == 'a' && uid.attributes['href'] == url)
				end
				if iz || (URI.parse(uid.text_content).normalize.to_s == url)
					return vcard
				end
			rescue Exception
			end
		end
		uid = vcard.at('a[@rel~=me]')
		unless uid.nil?
			return vcard
		end
		#the following works and is good for Twitter and others, but is not official part of representative hcard
		if vcard.name == 'address' || vcard.parent.name == 'address'
			return vcard
		end
	end

	nil

end

# db format is:
# hostname username password database
db_settings = open('/home/singpolyma/buddydb').read.split(/\s+/)

if ARGV[0] =~ /facebook\.com\/profile\.php/
	ARGV[0] = 'http://www.facebook.com/people/_/' + ARGV[0].scan(/facebook\.com\/profile\.php\?id=(\d+)$/)[0][0]
	page = `curl -s -L -A"Mozilla/4.0" "#{ARGV[0]}"`
	ARGV[0] = page.scan(/window.location.replace\("([^"]+)"\);/)[0][0].gsub!(/\\\//,'/')
end

begin
	#page = open(ARGV[0]).read
	page = `curl -s -L -A"Mozilla/5.0 (SocialWebSearch)" "#{ARGV[0]}"`
rescue Exception
	exit
end
if page == ''
	db = Mysql.new(db_settings[0],db_settings[1],db_settings[2],db_settings[3])
	db.real_query("SET NAMES 'UTF8'")
	db.real_query("DELETE FROM queue WHERE url='#{Mysql.quote(ARGV[0])}'")
	db.close
	exit
end
#page = Iconv.iconv('UTF-8//IGNORE','UTF-8',page)[0]
doc = Hpricot.parse(page)

uri = URI.parse(ARGV[0]).normalize
doc.search('a').each do |a|
	a_uri = URI.parse(a.attributes['href'].gsub(' ','%20')).normalize rescue URI.parse('')
	a_uri.scheme = uri.scheme if a_uri.scheme.nil?
	if a_uri.scheme =~ /https?|ftp/
		a_uri.host = uri.host if a_uri.host.nil?
		a_uri.path = "#{uri.path}#{a_uri.path}" if a_uri.path.nil? || a_uri.path[0..0] != '/'
	end
	a.set_attribute 'href', a_uri.to_s
end
doc.search('img').each do |a|
	a_uri = URI.parse(a.attributes['src'].gsub(' ','%20')).normalize rescue URI.parse('')
	a_uri.scheme = uri.scheme if a_uri.scheme.nil?
	if a_uri.scheme =~ /https?|ftp/
		a_uri.host = uri.host if a_uri.host.nil?
		a_uri.path = "#{uri.path}#{a_uri.path}" if a_uri.path.nil? || a_uri.path[0..0] != '/'
	end
	a.set_attribute 'src', a_uri.to_s
end

hcard = representative_hcard(doc, ARGV[0])

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
if fn == '' && hcard
	begin
		fn = hcard.at('.fn').inner_html.gsub(/<.*?>/,'').strip.gsub(/&amp;/,'&')
	rescue
	end
end

if hcard and !(uri.to_s =~ /facebook\.com/)
	hcard.search('.url').each do |link|
		urls.push link.attributes['href']
	end
end
doc.search('a[@rel~=me]').each do |link|
	urls.push link.attributes['href']
end
urls.uniq!

(JSON.parse(open("http://socialgraph.apis.google.com/lookup?q=#{urls.join(',')}&edo=1&edi=0&fme=1&sgn=0").read)['nodes'] rescue []).each do |k,v|
	urls.push k
	if v['nodes_referenced']
		v['nodes_referenced'].each do |node|
			if node[1]['types'].index('me').nil?
				contacts[node[0]] = {'rel' => node[1]['types'], 'doc' => nil}
			end
		end
	end
end
urls.uniq!

urls.each do |url|
	if url =~ /^mailto:/
		emails.push url.scan(/^mailto:(.*)$/)[0][0]
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

res = db.query("SELECT * FROM urls WHERE url='#{Mysql.quote(uri.to_s)}'")
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
		urls.push uri.to_s #if this one has already been verified, that counts as being verified
		res = db.query("SELECT * FROM urls WHERE verified=1 AND url IN ('#{urls.join('\',\'')}') LIMIT 1")
		verified = res.fetch_hash
		res.free
		
		unless verified.nil? or url_row['person_id'] != verified['person_id']
			person_id = verified['person_id']
			db.real_query("UPDATE urls SET verified=1,person_id=#{person_id} WHERE url='#{Mysql.quote(uri.to_s)}'")
			sql = []
			if fn.to_s != ''
				sql.push "fn='#{Mysql.quote(fn.to_s)}'"
			end
			if given_name.to_s != ''
				sql.push "`given-name`='#{Mysql.quote(given_name.to_s)}'"
			end
			if family_name.to_s != ''
				sql.push "`family-name`='#{Mysql.quote(family_name.to_s)}'"
			end
			if additional_name.to_s != ''
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
	db.real_query("INSERT IGNORE INTO fields (person_id,type,value) VALUES (#{person_id},'email','#{Mysql.quote(email)}')")
end

db.close
