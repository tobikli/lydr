let mix = require("laravel-mix");
const path = require("path");
const vueFrontendAlias = {
  "@vue-frontend": path.resolve(__dirname, "vue-frontend/src"),
};

mix
  .setPublicPath("./assets")
  .js('src/js/main.js', 'assets/js/main.min.js')
  .alias(vueFrontendAlias)
  .js('src/js/global-scripts.js', 'assets/js/global-scripts.min.js')
  .vue()
  .webpackConfig({
    resolve: {
      extensions: [".vue", ".ts", ".js"],
    },
    module: {
      rules: [
        {
          test: /\.ts$/,
          loader: "ts-loader",
          exclude: /node_module/,
          options: {
            appendTsSuffixTo: [/\.vue$/],
            compilerOptions: {
              isCustomElement: (tag) => {
                return tag === "hp-icon"; // need to adjust this
              },
            },
          },
        },
      ],
    },
  })
  .sass("src/css/style.scss", "assets/css/main.min.css")
  .sass("src/css/global.scss", "assets/css/global.min.css")
  .sass("src/css/preview/preview.scss", "assets/css/hts-preview.min.css")
  .options({
    processCssUrls: false,
  })
  .copy("src/images/**/*.{jpg,jpeg,png,gif,svg}", "assets/images")
  .copy("src/icons/**/*.{jpg,jpeg,png,gif,svg}", "assets/images")
  .copy("src/fonts/**/*.{ttf,woff2,woff}", "assets/fonts");
