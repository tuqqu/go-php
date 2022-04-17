package main

const (
	a = iota
	b = iota
	c = iota
)

const (
	d = iota + 3
	e
	f
)

const (
	g = 1 - iota
	h = 1 - iota
	i = 3
	j = 1 - iota
)

const (
	k, l uint8  = iota * 42, iota + 4
	m    uint64 = iota * 42
)

const n = iota

func main() {
	println(a, b, c, d, e, f, g, h, i, j, k, l, m, n)
}
