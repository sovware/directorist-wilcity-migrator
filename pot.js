const wpPot = require('wp-pot');
 
wpPot({
  destFile: './languages/wilcity-to-directorist-migrator.pot',
  domain: 'wilcity-to-directorist-migrator',
  package: 'Directorist Migrator',
  src: './**/*.php'
});