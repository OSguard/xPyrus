#!/bin/sh

#convert $1 -background '#000' -rotate 14 -resize 140x140 -transparent '#000' /tmp/foo.gif
convert $1 -background '#000' -resize 116x116 -rotate 14 -transparent '#000' /tmp/foo.gif
#convert $1 -background '#000' -rotate 14 -resize 135x135 -transparent '#000' /tmp/foo.gif
#convert \( -composite -compose atop -geometry +28+16 foto.png /tmp/foo.gif \) -fill '#196872' -pointsize 14 -font Whoosit.ttf -draw "rotate 15 translate 55 135 text 0,0 '$2'" $3
convert \( -composite -compose atop -geometry +${4}+${5} foto.png /tmp/foo.gif \) -fill '#196872' -pointsize 14 -font Whoosit.ttf -draw "rotate 15 translate 55 135 text 0,0 '$2'" $3
