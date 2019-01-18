var gulp        = require('gulp');            // Automatizador de tarefas
var sass        = require('gulp-sass');       // Compilador SASS
var less        = require('gulp-less');       // Compilador LESS
var cleanCSS    = require('gulp-clean-css');  // Minificador de arquivos CSS
var concat      = require('gulp-concat');     // Concatenador de arquivos
var rename      = require('gulp-rename');     // Ferramenta pra renomear arquivos
var uglify      = require('gulp-uglify');     // Minificador de arquivos JS
var plumber     = require('gulp-plumber');    // Lidando com erros
// var babel       = require('gulp-babel');      // Permitir a minificação de arquivos js escritos com padrão ES6


// Sass
gulp.task('estilos', function() {
  console.log("---- compilando estilos ----");
  gulp.src('./scss/style.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(cleanCSS({compatibility: 'ie9'}))
    .pipe(gulp.dest('./css/'))
});

// Javascript
var arquivosJs = [
  'js/plugins/jquery-2.1.3.js',
  'js/plugins/sweetalert.js',
  'js/plugins/touch.swipe.js',
  'js/plugins/validator.js',
  'js/plugins/funcoes.js',
  'js/plugins/jquery.maskedinput.min.js',
  'js/plugins/jquery.maskMoney.min.js',
  'js/plugins/jquery.infieldlabel.js',
  'js/plugins/mwheelIntent.js',
  'js/plugins/select2.min.js'
];

gulp.task('plugins', ()=>{
  console.log("---- compilando plugins ----");
  gulp.src(arquivosJs)
    .pipe(plumber())
    .pipe(concat('plugins.js'))
    .pipe(gulp.dest('js/dist/'))
    .pipe(rename('plugins.min.js'))
    .pipe(uglify().on('error', (e)=>{
      console.log(e);
    }))
    .pipe(gulp.dest('js/dist'));
});


gulp.task('global', ()=>{ 
  console.log("---- compilando global js ----");
  gulp.src('js/src/global.js')
    .pipe(plumber())
    .pipe(gulp.dest('js/dist/'))
    .pipe(rename('global.min.js'))
    .pipe(uglify().on('error', (e)=>{
      console.log(e);
    }))
    .pipe(gulp.dest('js/dist/'));
    gulp.start('bundle');
});

gulp.task('bundle', ()=>{
  console.log("---- Gerando bundle ----");
  gulp.src(['js/dist/plugins.min.js','js/dist/global.min.js'])
    .pipe(plumber())
    .pipe(concat('bundle.js'))
    .pipe(gulp.dest('js/dist/'));
});

// monitorando por mudanças
gulp.task('default',function() {
  // Rodar tarefas inicialmente e então continuar monitorando
  gulp.start('estilos');
  gulp.watch('./scss/**/*.scss',['estilos']);

  gulp.start('plugins');
  
  gulp.start('global');
  gulp.watch('js/src/global.js',['global']);
});