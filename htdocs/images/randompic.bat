@echo off
convert default_userpic.jpg -background "#000" -rotate 15 -resize 141x141 -transparent "#000" temp.gif
composite -compose atop -geometry +30-7 temp.gif random.gif -draw "fill #196872 font Whoosit.ttf translate 10 110 rotate 15 text 0,0 'Quietcheentschen'" test_random.gif
test_random.gif
pause
