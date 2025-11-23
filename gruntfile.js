module.exports = function(grunt) {
    // Project configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // Zip Export (only task needed)
        compress: {
            main: {
                options: {
                    archive: 'zamkai-yt-gallery.zip',  // e.g., tg-carousel-1.0.0.zip
                    mode: 'zip'
                },
                files: [
                    // Includes: Core files in main directory
                    { src: ['*.php', 'readme.txt'], dest: '/', filter: 'isFile' },

                    // Includes: Specified folders (recursive)
                    { src: ['css/**'], dest: '/' },
                    { src: ['build/**'], dest: '/' },
                    { src: ['includes/**'], dest: '/' },
                    { src: ['templates/**'], dest: '/' },
                    { src: ['.wordpress.org/**'], dest: '/' },

                    // Excludes: Dev junk (add more if needed)
                    { src: ['!node_modules/**'], dest: '/' },
                    { src: ['!.git/**'], dest: '/' },
                    { src: ['!Gruntfile.js'], dest: '/' },
                    { src: ['!vendor/**'], dest: '/' },
                    { src: ['!package*.json'], dest: '/' }
                ]
            }
        }
    });

    // Load task
    grunt.loadNpmTasks('grunt-contrib-compress');

    // Default task: Just zip
    grunt.registerTask('default', ['compress']);
};
