#!/usr/bin/ruby

require 'lib/fetch_store_profile'

# db format is:
# hostname username password database
db_settings = open(ENV['HOME'] + '/socialsearchme.sql').read.split(/\s+/)
db = Mysql.new(db_settings[0],db_settings[1],db_settings[2],db_settings[3])
db.reconnect = true
db.real_query("SET NAMES 'UTF8'")

$queue = ['http://singpolyma.net']
2.times do
0.upto($queue.length-1) do |i|
	item = $queue.shift
	puts "Crawling #{item}..."
	fetch_store_profile(item, db)
end
puts $queue.join("\n")
puts '$$$$'
end


db.close
