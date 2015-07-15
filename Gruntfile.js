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
    }
  });

  grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
  grunt.loadNpmTasks('grunt-replace');

  // Default task(s).
  grunt.registerTask('default', ['wp_readme_to_markdown', 'replace']);
};
