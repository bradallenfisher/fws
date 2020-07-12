var gulp          = require('gulp'),
    autoprefixer  = require('autoprefixer')
    postcss       = require('gulp-postcss');
    sourcemaps    = require('gulp-sourcemaps'),
    uglify        = require('gulp-uglify'),
    sass          = require('gulp-sass'),
    concat        = require('gulp-concat'),
    sassGlob      = require('gulp-sass-glob'),

    input  = {
      'scss': 'src/scss/**/*.scss',
      'js': 'src/js/**/*.js',
    },

    output = {
      'css': 'css',
      'js': 'js'
    };

/* run the watch task when gulp is called without arguments */
gulp.task('default', ['watch']);

/* run javascript through jshint */
gulp.task('jshint', function() {
  return gulp.src(input.js)
    .pipe(jshint())
    .pipe(jshint.reporter('jshint-stylish'));
});

/* compile scss files */
gulp.task('sass', function() {
  return gulp.src('src/scss/**/*.scss')
    .pipe(sassGlob())
    .pipe(sass())
    .pipe(postcss([autoprefixer()]))
    .pipe(gulp.dest(output.css));
});

/* concat javascript files, minify if --type production */
gulp.task('js', function() {
  return gulp.src(input.js)
    .pipe(concat('scripts.min.js'))
    .pipe(gulp.dest(output.js));
});

/* Watch these files for changes and run the task on update */
gulp.task('watch', function() {
  gulp.watch(input.js, ['jshint', 'build-js']);
  gulp.watch(input.scss, ['sass']);
});