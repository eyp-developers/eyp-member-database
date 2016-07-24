#! /bin/sh

# Clear build directory
rm -rf dist
mkdir dist

# Create basic files
cp -r backend dist/
cp -r files dist/
cp -r fonts dist/
cp -r img dist/
mkdir dist/uploads
chmod 777 dist/uploads
mkdir dist/js
cp index.html dist/

# Javascript files
find js/frameworks -iname "*.js" -exec cat {} >> dist/js/frameworks.js \;
uglifyjs dist/js/frameworks.js -o dist/js/frameworks.min.js
rm dist/js/frameworks.js

find js/custom -iname "*.js" -exec cat {} >> dist/js/custom.js \;
uglifyjs dist/js/custom.js -o dist/js/custom.min.js
rm dist/js/custom.js

# CSS files
find css -iname "*.css" -exec cat {} >> dist/all.css \;

# Fix index.html
perl -0777 -i.original -pe 's/<!-- BEGIN: CSS -->.*<!-- END: CSS -->/<link href="all.css" rel="stylesheet">/gs' dist/index.html
perl -0777 -i.original -pe 's/<!-- BEGIN: JS -->.*<!-- END: JS -->/<script src="js\/frameworks.min.js"><\/script>\n<script src="js\/custom.min.js"><\/script>/gs' dist/index.html
rm dist/index.html.original