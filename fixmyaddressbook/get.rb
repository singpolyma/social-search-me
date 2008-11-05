require 'net/http'
require 'uri'

def get(uri)
	@c = 0 if @c.nil?
	@c += 1
	return '' if @c > 10
	uri = URI.parse(uri) if uri.is_a?String
	res = Net::HTTP.new(uri.host, uri.port).start { |http| http.request(Net::HTTP::Get.new("#{uri.path}?#{uri.query}", {'User-Agent' => 'Mozilla/5.0 (FixMyAddressbook)'})) }
	return get(res['location']) if res.key?'location'
	res.body
end

