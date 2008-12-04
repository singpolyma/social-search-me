require 'rubygems'
require 'hpricot'
require 'json'

def hcard_urls(hcard, doc=nil, uri=nil)

	urls = []
	contacts = {}
	doc = hcard if doc.nil?

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

	return urls, contacts

end
