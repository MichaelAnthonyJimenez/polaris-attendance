import postcssImport from 'postcss-import';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';

export default {
    plugins: [
        // make sure postcss-import runs first so that any legitimate
        // `@import` rules are resolved before Tailwind processes the
        // directives.  We also add a filter that prevents the plugin from
        // trying to load the Tailwind package itself – the library exposes a
        // JS entrypoint, and importing it as CSS is what was triggering the
        // "Unknown word \"use strict\"" error during the build.
        postcssImport({
            filter(id) {
                console.log("postcss-import filter received id:", id);
                // skip anything coming from the tailwindcss package or any
                // path that ends in a .js file (we only want to handle CSS).
                if (typeof id === 'string') {
                    if (id.startsWith('tailwindcss')) {
                        return false;
                    }
                    if (id.endsWith('.js')) {
                        return false;
                    }
                }
                return true;
            },
        }),
        tailwindcss(),
        autoprefixer(),
    ],
};
