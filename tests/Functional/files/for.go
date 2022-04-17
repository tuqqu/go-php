package main

func main() {
	var x int

	for {
		x++
		if x > 4 {
			break
		}
		if x == 2 {
			continue
		}

		println(x)

	}

	for y := 10; y > 5; y -= 2 {
		var u = y * 2

		println(y - u)
	}

	var d = 10
	for d > 3 {
		println(d)
		d -= 4
	}

	var c = 0
	for ; true; c++ {
		if c > 2 {
			break
		}

		println(c)
	}
}
