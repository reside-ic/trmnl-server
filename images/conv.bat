@echo off

rem Usage: conv.bat input.png output.png

magick -size 4x1 xc:#000000 xc:#555555 xc:#aaaaaa xc:#ffffff +append -type Palette colormap-2bit.png
magick %1 -resize 800x480\! -dither FloydSteinberg -remap colormap-2bit.png -define png:bit-depth=2 -define png:color-type=0 %2
