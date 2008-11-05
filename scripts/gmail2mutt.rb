#!/usr/bin/ruby
require 'iconv'

names = []

Iconv.iconv('UTF-16//IGNORE','UTF-8',File.open(ARGV[0]).read)[0].gsub(/\000/,'').sub(/^../,'').each("\n") do |line|
   f = line.chomp.split(/"?,"?/)
   f[1] = f[1].split('@')[0].sub('%','@') if f[1] =~ /%/
   f[0] = f[1] if f[0].to_s == ''
   next if f[0] == 'Name'
   puts "alias \"#{f[1]}\" \"#{f[0]}\" <#{f[1]}>"
   next unless names.index(f[0]).nil?
   puts "alias \"#{f[0].gsub(' ','')}\" \"#{f[0]}\" <#{f[1]}>"
   names << f[0]
end
