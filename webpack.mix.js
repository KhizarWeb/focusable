let mix = require("laravel-mix");

mix.browserSync({
  proxy: "http://wordpress.two",
  files: ["**/*.php", "assets/dist/css/**/*.css", "assets/dist/js/**/*.js"],
  injectChanges: false,
});

mix.setPublicPath("./assets/dist");

mix
  .js("assets/src/js/main.js", "assets/dist/js")
  .sass("assets/src/sass/style.scss", "assets/dist/css")
  .options({
    processCssUrls: false,
  });
