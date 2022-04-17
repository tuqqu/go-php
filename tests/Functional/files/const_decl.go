package main

const a = 1
const b string = "string"
const (
	c       int = a * 2
	d, e, f     = "string", 1, true
)

func main() {
	println(a, b, c, e, f)

	const a uint8 = 2
	const (
		b bool = true
		c      = a * 2
	)

	println(a, b, c)

	const (
		d, e, f string = "str1", "str2", "str3"
	)
	const g, h, i uint32 = 1, 2, 3

	println(d, e, f, g, h, i)

	const j bool = true

	println(j)

	{
		const j = 100
		{
			println(j)
			const j = 88
			println(j)
		}
		println(j)
	}
	println(j)
}
