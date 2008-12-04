#!/usr/bin/ruby

ENV['GEM_PATH'] = '/home/singpolyma/.gems:/usr/lib/ruby/gems/1.8'

require 'rubygems'
require 'open-uri'
require 'hpricot'
require 'uri'
require 'net/http'
require 'rexml/document'
require 'oauth/consumer'

def representative_hcard(doc, url)

	url = URI.parse(url).normalize.to_s

	doc.search('.vcard').each do |vcard|
		uid = vcard.at('.uid')
		unless uid.nil?
			if (uid.name == 'a' && URI.parse(uid.attributes['href']).normalize.to_s == url) || (URI.parse(uid.text_content).normalize.to_s == url)
				return vcard
			end
		end
		uid = vcard.at('*[@rel~=me]')
		unless uid.nil?
			return vcard
		end
	end

end

class XRDS_Simple_XRD
	def initialize(xrd)
		@xrd = xrd
	end

	def services_by_types(types=[],mediatypes=[])
		rtrn = []
		@xrd.each_element('./Service') do |service|
			service_types = []
			service_media_types = []
			service.each_element('./Type') { |type| service_types.push type.get_text }
			service.each_element('./MediaType') { |type| service_media_types.push type.get_text }
			iz = true
			types.each { |type| iz = iz && !service_types.index(type).nil? }
			mediatypes.each { |type| iz = iz && !service_media_types.index(type).nil? }
			rtrn.push service if iz
		end
		rtrn
	end
end

class XRDS_Simple
	def initialize(doc)#doc = XRDS-Simple document string
		@xrds = {}
		doc = REXML::Document.new(doc)
		doc.each_element('//XRD') do |xrd|
			if xrd.get_elements('./Type')[0].get_text == 'xri://$xrds*simple'
				id = xrd.attributes['id']
				id = Time.now.to_i unless id
				@xrds[id] = XRDS_Simple_XRD.new(xrd)
				@default = @xrds[id]
			end
		end
	end

	def [](key)
		@xrds[key]
	end

	def default
		@default
	end

	def self.resolve(url)
		uri = URI.parse(url)
		res = Net::HTTP.start(uri.host, uri.port) {|http|
			http.request_get(uri.request_uri, {'Accept' => 'application/xrds+xml'})
		}
		if res['Content-Type'] =~ /application\/xrds\+xml/
			return self.new(res.body)
		else
			url = false
			url = res['X-XRDS-Location'] if res['X-XRDS-Location']
			url = Hpricot.parse(res.body).at('//meta[@http-equiv="X-XRDS-Location"]').attributes['content'] unless url
			if url
				uri = URI.parse(url)
				res = Net::HTTP.start(uri.host, uri.port) {|http|
					http.request_get(uri.request_uri, {'Accept' => 'application/xrds+xml'})
				}
				return self.new(res.body) if res['Content-Type'] =~ /application\/xrds\+xml/
			end
		end
		nil
	end
end

def oauth_discovery(url)
	xrdss = XRDS_Simple.resolve(url)
	oauth_discovery_url = xrdss.default.services_by_types(['http://oauth.net/discovery/1.0'])[0].get_elements('./URI')[0].get_text
	if oauth_discovery_url.to_s[0,1] == '#'
		return xrdss[oauth_discovery_url.to_s.reverse.chop.reverse]
	else
		return XRDS_Simple.resolve(oauth_discovery_url).default
	end
end

url = ARGV[0]

oauth_endpoints = oauth_discovery(url)
request = authorize = access = key = nil
oauth_endpoints.services_by_types(['http://oauth.net/core/1.0/endpoint/request'])[0].each_element('./URI') {|uri| request = uri.get_text.to_s.chomp}
oauth_endpoints.services_by_types(['http://oauth.net/core/1.0/endpoint/authorize'])[0].each_element('./URI') {|uri| authorize = uri.get_text.to_s.chomp}
oauth_endpoints.services_by_types(['http://oauth.net/core/1.0/endpoint/access'])[0].each_element('./URI') {|uri| access = uri.get_text.to_s.chomp}
oauth_endpoints.services_by_types(['http://oauth.net/discovery/1.0/consumer-identity/static'])[0].each_element('./LocalID') {|id| key = id.get_text.to_s.chomp}

consumer = OAuth::Consumer.new(key,nil,{
		:site => url,
		:scheme => :query_string,
		:http_method => :get,
		:request_token_path => URI.parse(request).path,
		:authorize_path => URI.parse(authorize).path,
		:access_token_path => URI.parse(access).path
	})

#OAuth::RequestToken.new(consumer, token, secret)
#OAuth::AccessToken.new(consumer, token, secret)

access_token = nil
case ARGV[1]
	when 'start':
		request_token = consumer.get_request_token
		puts "#{request_token.authorize_url} #{request_token.token} #{request_token.secret}"
		exit
	when 'access':
		request_token = OAuth::RequestToken.new(consumer, ARGV[2], ARGV[3])
		access_token = request_token.get_access_token
end

page = access_token.get(URI.parse(url).path).body
doc = Hpricot.parse(page)
hcard = representative_hcard(doc, url)
puts hcard.to_html
