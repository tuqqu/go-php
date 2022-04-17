package main

var a = 1
var b string = "string"
var c int = a * 2
var d, e, f = "string", 1, true

func main() {
	println(a, b, c, e, f)

	var a uint8 = 2
	var b bool = true
	var c = a * 2

	println(a, b, c)

	var d, e, f string
	var g, h, i uint32 = 1, 2, 3

	println(d, e, f, g, h, i)

	j := 1
	k, l, m := true, false, 3

	println(j, k, l, m)

	{
		j := false
		{
			println(j)
			var j = 88
			println(j)
		}
		println(j)
	}
	println(j)
}
