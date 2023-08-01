package main

func main() {
	test_1()
}

func test_1() {
	println("test_1")

	strings := []string{
		"hello world",
		"hello",
		"2dsùî343..'dsf[sdf][sd][f]][234sdsùîó",
		"2ïöppqdßd][f]][234sdsùîó",
		"你好",
		"こんにちは",
		"_",
		"___",
		".",
	}

	compareStrings(strings)
}

func compareStrings(strings []string) {
	for i := 0; i < len(strings); i++ {
		for j := 0; j < len(strings); j++ {
			if i == j {
				continue
			}

			if strings[i] == strings[j] {
				println(strings[i], "==", strings[j])
			}

			if strings[i] != strings[j] {
				println(strings[i], "!=", strings[j])
			}

			if strings[i] > strings[j] {
				println(strings[i], ">", strings[j])
			}

			if strings[i] < strings[j] {
				println(strings[i], "<", strings[j])
			}
		}
	}
}
