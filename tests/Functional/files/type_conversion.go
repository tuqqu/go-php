package main

func main() {
	var a int = 1
	var b uint = uint(a)
	var c float32 = float32(b + uint(a))
	var d float64 = 54.4 + float64(c) + float64(a)
	var e int8 = int8(d)
	println(a, b, c, d, e)

	var f byte = 'a'
	var g uint8 = 250
	var h uint64 = 'ё'
	var i rune = 'e'
	var j = 'ï'
	var k = j + i
	var l = string([]byte{'h', 'e', 'l', 'l', 'o'}) + " world"
	var m = string([]rune{'h', i, j, k})
	var n = string(g)
	var o = string(f)

	println(f, g, h, i, j, k, l, m, n, o)
}
