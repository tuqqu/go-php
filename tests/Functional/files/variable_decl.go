package main

func main() {
	test_var_decl()
	test_short_var_decl()
	test_mixed()
}

var a = 1
var b string = "string"
var (
	c       int = a * 2
	d, e, f     = "string", 1, true
)

func test_var_decl() {
	println("test_var_decl")

	println(a, b, c, e, f)

	var a uint8 = 2
	var (
		b bool = true
		c      = a * 2
	)

	println(a, b, c)

	var (
		d, e, f string
	)
	var g, h, i uint32 = 1, 2, 3

	println(d, e, f, g, h, i)

	var j = 1
	var k, l, m = true, false, 3

	println(j, k, l, m)

	{
		var j = false
		{
			println(j)
			var j = 88
			println(j)
		}
		println(j)
	}
	println(j)
}

func test_short_var_decl() {
	println("test_short_var_decl")

	j := 1
	k, l, m, j := true, false, 3, 0

	println(j, k, l, m)

	v, w := "hi", []int{1, 2, 3}
	v, w, x := "hello", []int{65, 66, 67}, true

	println(v, w[0], x)

	{
		j := false
		{
			println(j)
			j := 88
			println(j)
		}
		println(j)
	}
	println(j)
}

func test_mixed() {
	println("test_mixed")

	var j = 1
	k, l, m, j := true, false, 3, 0
	println(j, k, l, m)

	v, w := "hi", []int{1, 2, 3}
	v, w, x := "hello", []int{65, 66, 67}, true

	println(v, w[0], x)
}
