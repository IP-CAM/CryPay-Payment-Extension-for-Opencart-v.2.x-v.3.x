#!/bin/sh
#  Run script and pass version: ./op-release.sh

set -e

#enviroment variables
tmp_dir=tmp
release_name=crypay-opencart.ocmod.zip
release_dir_name=crypay-opencart

echo "Starting process..."

if [ -d  "${tmp_dir}" ]; then
  rm -rf "${tmp_dir}"
fi

rm -rf ${release_dir_name}-*.zip

mkdir $tmp_dir
rsync -a . ${tmp_dir}/${release_name}

echo "Removing unnecessary files..."

cd ${tmp_dir}/${release_name}
rm -rf .git .github .gitignore .DS_Store .idea vendor .phpcs.xml tmp op-release.sh
cd ../../

echo "Compressing release folder..."

cd $tmp_dir && zip -r "${release_dir_name}-$1.zip" ${release_name} && cd ..
mv "${tmp_dir}/${release_dir_name}-$1.zip" .
rm -rf $tmp_dir

echo ""
echo "Release folder is completed."
echo ""