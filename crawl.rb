#!/usr/bin/ruby

require 'lib/fetch_store_profile'

# db format is:
# hostname username password database
db_settings = open(ENV['HOME'] + '/socialsearchme.sql').read.split(/\s+/)
db = Mysql.new(db_settings[0],db_settings[1],db_settings[2],db_settings[3])
db.reconnect = true
db.real_query("SET NAMES 'UTF8'")

$queue = open(ENV['HOME'] + '/.socialsearchmequeue').read.split(/\n/)
$queue = ['http://singpolyma.net'] unless $queue and $queue.length > 0
loop do
	0.upto($queue.length-1) do |i|
		item = $queue.shift
		puts "Crawling #{item}..."
		fetch_store_profile(item, db)
	end
	$queue.uniq!
	fh = File.new(ENV['HOME'] + '/.socialsearchmequeue', 'w')
	fh.write($queue.join("\n"))
	fh.close
end

db.close
