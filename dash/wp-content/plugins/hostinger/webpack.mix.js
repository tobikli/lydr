let mix = require("laravel-mix");

mix
  .setPublicPath("./assets")
  .sass("src/css/components/plugin-rating.scss", "assets/css/plugin-rating.min.css")
  .sass("src/css/components/coming-soon.scss", "assets/css/coming-soon.min.css")
  .sass("src/css/global.scss", "assets/css/hostinger-global.min.css")
  .js("src/js/global-scripts.js", "assets/js/hostinger-global-scripts.min.js")
  .options({
    processCssUrls: false,
  })
  .copy("src/images/**/*.{jpg,jpeg,png,gif,svg}", "assets/images")
  .copy("src/fonts/**/*.{ttf,woff2,woff}", "assets/fonts");
