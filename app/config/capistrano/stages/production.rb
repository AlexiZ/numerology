set :stage, :production
set :domain, 'www.numerologique.fr'

server fetch(:domain), user: 'numerologique.fr', roles: %w{web app db}, port: 2222

set :deploy_to, '/home/numerologique.fr'
set :branch, 'master'

set :composer_install_flags, '--no-dev --prefer-dist --no-interaction --quiet --optimize-autoloader --ignore-platform-reqs'
set :composer_php, 'php'

SSHKit.config.command_map[:composer] = "#{fetch(:deploy_to)}/shared/composer.phar"
SSHKit.config.command_map[:composer] = "#{shared_path.join("composer.phar")}"
SSHKit.config.command_map[:php] = "php"
