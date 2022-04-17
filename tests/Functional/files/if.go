package main

func main() {
	if x := 5; x > 1 {
		println(1)
	}

	if !true {
		println(2)
	} else if y := 3; y == 3 {
		println(3)
	} else {
		println(4)
	}

	if 4 < 3 {
		println(5)
	} else {
		println(6)
	}
}
