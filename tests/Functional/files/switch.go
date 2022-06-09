package main

func main() {
	test_switch_1()
	test_switch_2()
	test_switch_3()
	test_switch_4()
	test_switch_5()
	test_switch_6()
	test_switch_7()
	test_switch_8()
}

// empty
func test_switch_1() {
	println("test_switch_1")

	switch {
	}
}

// switch with no condition
func test_switch_2() {
	println("test_switch_2")

	switch {
	case false:
		println("false")
	case true:
		println("true")
	}
}

// case matched
func test_switch_3() {
	println("test_switch_3")

	switch 35 {
	case 30, 31, 32 + 0:
		println("30, 31, 32")
	case 33, 34 + 1:
		println("33, 34 + 1")
	case 36:
		println("36")
	default:
		println("default")
	}
}

// cases not matched, all functions called
func test_switch_4() {
	println("test_switch_4")

	switch i := 1; i * 10 {
	case i:
		println("i")
	case i * i, return100():
		println("i * i")
	case return100():
		println("35")
	}
}

// default case matched
func test_switch_5() {
	println("test_switch_5")

	switch i := 1; i * 10 {
	default:
		println("default")
	case i:
		println("i")
	case i * i, return100():
		println("i * i")
	case return100():
		println("35")
	}
}

// case matched, functions don't get called
func test_switch_6() {
	println("test_switch_6")

	switch i := 1; i * 100 {
	default:
		println("default")
	case 100, return100():
		println("100")
	case return100():
		println("35")
	}
}

// fallthrough and break
func test_switch_7() {
	println("test_switch_7")

	switch i := 1; i * 100 {
	default:
		println("default")
		fallthrough
	case 101:
		println("101")
		break
		println("101")
	case 102:
		println("102")
	}
}

// nested switch in loop
func test_switch_8() {
	println("test_switch_8")

	for {
		switch i := 1; i * 100 {
		default:
			println("default")
			fallthrough
		case 100:
			println("100")
			fallthrough
		case 101:
			switch j := i; j {
			case 100:
				println("inner 100")
				fallthrough
			case 101:
				println("inner 101")
				fallthrough
			case 102:
				println("inner 102")
				break
			case 103:
				println("inner 103")
			}
			println("101")
			fallthrough
		case 102:
			println("102")
			break
		case 103:
			println("103")
		}

		println("end for")
		break
	}
}

func return100() int {
	println("return100")

	return 100
}
