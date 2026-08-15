import postcss from 'postcss';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';
import fs from 'fs';

async function build() {
    const css = fs.readFileSync('resources/css/app.css', 'utf8');
    const result = await postcss([
        tailwindcss('./tailwind.config.js'),
        autoprefixer
    ]).process(css, { from: 'resources/css/app.css', to: 'public/build/assets/app-DRlSmaHB.css' });
    
    fs.writeFileSync('public/build/assets/app-DRlSmaHB.css', result.css);
    console.log('✅ CSS compiled to public/build/assets/app-DRlSmaHB.css (' + (result.css.length / 1024).toFixed(2) + ' KB)');
}

build().catch(err => {
    console.error(err);
    process.exit(1);
});
