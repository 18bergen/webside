#!/usr/bin/env python

f = open("borringlesson.gdf", "rb");
d = open("borringlessonc.gdf", "wb");

for i in xrange(4):
        b = [f.read(1) for j in xrange(4)];
        b.reverse();
        d.write(''.join(b));

d.write(f.read());