#! /bin/sh

# Ask for version
echo "Which version do you want to build?"
read version

# Ask for localization
localizations=(
	"at"
	"de"
	"dk"
)

localized_files=(
	"backend/_Modules/press/data.json"
	"backend/_Modules/schools/data.json"
	"backend/_Modules/sponsors/data.json"
	"img/eyp_logo_white.png"
)

echo "Which localization do you want to build?"
read localization

if [[ ! " ${localizations[@]} " =~ " ${localization} " ]]; then
	echo "I'm sorry Dave, I'm afraid I can't do that..."
	exit 1
fi

# Clear build directory
echo "-> Clearing build directory"
rm -rf dist
mkdir dist

# Create basic files
echo "-> Copying files"
cp -r backend dist/
cp -r files dist/
cp -r fonts dist/
cp -r img dist/
cp -r installer dist/
mkdir dist/uploads
chmod 777 dist/uploads
mkdir dist/js
cp index.html dist/

# Perform localization
echo "-> Performing localization"
for f in "${localized_files[@]}"
do
	mv "dist/$f.$localization.localized" "dist/$f"
done

find dist/ -iname "*.localized" -exec rm {} \;

# Javascript files
echo "-> Minimizing files"
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

# Package
echo "-> Packaging release"
zip -r "eyp-md-$version-$localization.zip" dist/ > /dev/null
rm -rf dist/

# Done
echo "-> Done. Please check the eyp-md-$version-$localization.zip file for your build."
