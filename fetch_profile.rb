#!/usr/bin/ruby

require 'lib/fetch_store_profile'

# db format is:
# hostname username password database
db_settings = open(ENV['HOME'] + '/socialsearchme.sql').read.split(/\s+/)
db = Mysql.new(db_settings[0],db_settings[1],db_settings[2],db_settings[3])
db.real_query("SET NAMES 'UTF8'")

$queue = []
fetch_store_profile ARGV[0], db
puts "Queue would be: #{$queue.inspect}"

db.close
