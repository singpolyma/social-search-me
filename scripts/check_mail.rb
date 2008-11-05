#!/usr/bin/ruby

require 'rubygems'
require 'json'
require 'net/pop'

messages = []

pop = Net::POP3.new('localhost', 110)
pop.start('singpolyma', 'PASSWORD') do
	if pop.mails.empty?
		puts '{}'
	else
		pop.each_mail do |m|
			next if m.deleted?
			data = {}
			message = m.pop.split /\r\n\r\n/, 2
			data['Body'] = message[1]
			last_key = ''
			message[0].split(/\r\n/).each do |h|
				if h[0..0] =~ /\s/
					data[last_key] += "\r\n#{h}"
				else
					tmp = h.split /:\s*/, 2
					data[tmp[0]] = tmp[1]
					last_key = tmp[0]
				end
			end
			messages.push data
		end
	end
end

puts messages.to_json
