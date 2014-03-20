require 'rake/clean'

html_files = Rake::FileList['**/*.haml'].ext('.html')
CLOBBER.include html_files

css_files  = Rake::FileList['**/*.scss'].ext('.css')
CLOBBER.include css_files

task default: :build

desc 'Build all files'
task build: ['build:html', 'build:css']

namespace :build do
  desc 'Build html files'
  task html: html_files

  desc 'Build css files'
  task css: css_files
end

desc 'Rebuild files'
task rebuild: [:clobber, :build]

rule '.html' => '.haml' do |t|
  sh "haml -t ugly #{t.source} #{t.name}"
end

rule '.css' => '.scss' do |t|
  sh "scss -t compressed #{t.source} #{t.name}"
end
