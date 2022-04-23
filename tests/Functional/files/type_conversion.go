package main

func main() {
	var a int = 1
	var b uint = uint(a)
	var c float32 = float32(b + uint(a))
	var d float64 = 54.4 + float64(c) + float64(a)
	var e int8 = int8(d)
	println(a, b, c, d, e)
}
