package main

func main() {
	for i, x := range "string" {
		println(i, x)
	}

	for x := range [3]int{1, 2, 3} {
		println(x)
	}

	nums := []int{4, 5, 6}
	i, x := 0, 0

	for i, x = range nums {
		println(i, x)
	}

	kvs := map[string]string{"a": "apple", "b": "banana"}
	for k, v := range kvs {
		println(k, v)
	}

	for range []int{1, 2} {
		println(99)
	}
}
