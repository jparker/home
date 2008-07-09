desc 'Deploy site'
task :deploy do
  system %[
    rsync -a --exclude=.DS_Store --exclude='.git*' . papango.urgetopunt.com:public_html
  ]
end
