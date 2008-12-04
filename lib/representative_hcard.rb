require 'rubygems'
require 'hpricot'

def representative_hcard(doc, url)

	uri = URI.parse(url.strip).normalize
	url = uri.to_s

	cards = doc.search('.vcard')
	return cards[0] if cards.size == 1

	address_card = nil

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
			address_card = vcard
		end
	end

	address_card

end

