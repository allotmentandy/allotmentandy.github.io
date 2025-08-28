#!/bin/bash
files=*.jpg
minimumWidth=600
minimumHeight=480

for f in $files
do
    imageWidth=$(identify -format "%w" "$f")
    imageHeight=$(identify -format "%h" "$f")

    if [ "$imageWidth" -gt "$minimumWidth" ] || [ "$imageHeight" -gt "$minimumHeight" ]; then
        mogrify -quality 60 -resize ''"$minimumWidth"x"$minimumHeight"'' $f
    fi
done