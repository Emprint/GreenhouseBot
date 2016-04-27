#!/bin/bash
#change directory
cd "$(dirname "$0")"

echo "Removing old pictures"
rm -f backup/*.jpg
rm -f temp/*.jpg

echo "Moving files"
mv images/*.jpg backup/

cd backup
echo "Putting timestamp and renaming"
x=1
for i in *.jpg; do
#   clear
   time="$(echo $i | cut -d . -f 1)"
   text="${time//:/\\\:}"
   counter=$(printf %05d $x)
   ffmpeg -i "$i" -vf "drawtext=fontfile=/usr/share/fonts/truetype/ttf-dejavu/DejaVuSans.ttf:fontcolor=white:x=5:y=5:text=$text" "../temp/$counter.jpg"
   let x=x+1
done
cd ..

echo "Creating video"
ffmpeg -qscale 5 -i temp/%05d.jpg videos/"${time//:/-}".mp4
