#!/usr/bin/ruby

ENV['GEM_PATH'] = '/home/singpolyma/.gems:/usr/lib/ruby/gems/1.8'

require 'rubygems'
require 'mysql'

# db format is:
# hostname username password database
db_settings = open('/home/singpolyma/buddydb').read.split(/\s+/)
db = Mysql.new(db_settings[0],db_settings[1],db_settings[2],db_settings[3])

res = db.query("SELECT * FROM contacts WHERE person_id=#{ARGV[0].to_i}")

res.each_hash do |row|
	Process.detach(fork { `ruby fetch_profile.rb "#{row['url']}" &` })
	puts "#{row['url']} updating..."
end

res.free

db.close
