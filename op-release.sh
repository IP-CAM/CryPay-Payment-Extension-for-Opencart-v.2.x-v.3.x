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

rm -rf  crypay-opencart2.ocmod.zip
rm -rf  crypay-opencart2_3.ocmod.zip
rm -rf  crypay-opencart3.ocmod.zip

mkdir $tmp_dir
rsync -a ./opencart2-plugin ${tmp_dir}
rsync -a ./opencart2.3-plugin ${tmp_dir}
rsync -a ./opencart3-plugin ${tmp_dir}


echo "Compressing release folder..."


cd $tmp_dir/opencart2-plugin && zip -r "crypay-opencart2.ocmod.zip" upload && cd ..
cd opencart2.3-plugin && zip -r "crypay-opencart2_3.ocmod.zip" upload && cd ..
cd opencart3-plugin && zip -r "crypay-opencart3.ocmod.zip" upload && cd ../..

mv "$tmp_dir/opencart2-plugin/crypay-opencart2.ocmod.zip" .
mv "$tmp_dir/opencart2.3-plugin/crypay-opencart2_3.ocmod.zip" .
mv "$tmp_dir/opencart3-plugin/crypay-opencart3.ocmod.zip" .
rm -rf $tmp_dir

echo ""
echo "Release folder is completed."
echo ""