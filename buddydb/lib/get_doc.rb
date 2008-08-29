require 'rubygems'
require 'hpricot'

def get_doc(uri)

	if uri.to_s =~ /facebook\.com\/profile\.php/
		uri.to_s = 'http://www.facebook.com/people/_/' + uri.to_s.scan(/facebook\.com\/profile\.php\?id=(\d+)$/)[0][0]
		page = `curl -s -L -A"Mozilla/4.0" "#{uri.to_s}"`
		uri.to_s = page.scan(/window.location.replace\("([^"]+)"\);/)[0][0].gsub!(/\\\//,'/')
	end

	begin
		#page = open(uri.to_s).read
		page = `curl -s -L -A"Mozilla/5.0 (SocialWebSearch)" "#{uri.to_s}"`
	rescue Exception
		return nil
	end
	
	if page == ''
		return nil
	end

	doc = Hpricot.parse(page)
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

	doc

end
