module.exports = function(grunt) {
  // Project configuration.
  grunt.initConfig({
    wp_readme_to_markdown: {
      target: {
        files: {
          'README.md': 'readme.txt'
        },
      },
    },
    replace: {
      dist: {
        options: {
          patterns: [
            {
              match: /^/,
              replacement: '[![WordPress](https://img.shields.io/wordpress/v/host-meta.svg?style=flat-square)](https://wordpress.org/plugins/host-meta/) [![WordPress plugin](https://img.shields.io/wordpress/plugin/v/host-meta.svg?style=flat-square)](https://wordpress.org/plugins/host-meta/changelog/) [![WordPress](https://img.shields.io/wordpress/plugin/dt/host-meta.svg?style=flat-square)](https://wordpress.org/plugins/host-meta/) \n\n'
            }
          ]
        },
        files: [
          {
            src: ['README.md'],
            dest: './'
          }
        ]
      }
    },
    makepot: {
      target: {
        options: {
          mainFile: 'host-meta.php',
          domainPath: '/languages',
          exclude: ['bin/.*', '.git/.*', 'vendor/.*'],
          potFilename: 'host-meta.pot',
          type: 'wp-plugin',
          updateTimestamp: true
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
  grunt.loadNpmTasks('grunt-replace');
  grunt.loadNpmTasks('grunt-wp-i18n');

  // Default task(s).
  grunt.registerTask('default', ['wp_readme_to_markdown', 'replace', 'makepot']);
};
