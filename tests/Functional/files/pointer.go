package main

func main() {
	test_pointer_int()
	test_pointer_array()
	test_pointer_map()
}

func test_pointer_int() {
	i := 1

	println(i)

	zeroval(i)

	println(i)

	zeroptr(&i)

	println(i)

	*&i = 6

	var pi *int = &i
	println(*pi == i, *pi, *&*&*&i)
}

func test_pointer_array() {
	var array = [...]int{1, 2, 3}

	plusTen(&array[1])

	println(array)

	var d *int = &array[2]
	var dptr **int = &d
	var dptrptr ***int = &dptr

	plusTen(**dptrptr)

	println(array)
}

func test_pointer_map() {

	var s1ptr string = "string1"
	var s2ptr string = "string2"

	var m = map[string]*string{
		"key1": &s1ptr,
		"key2": &s2ptr,
	}

	setstr(&s1ptr)

	println(*m["key1"], *m["key2"])
}

func zeroval(ival int) {
	ival = 0
}

func zeroptr(iptr *int) {
	*iptr = 0
}

func plusTen(iptr *int) {
	*iptr += 10
}

func setstr(str *string) {
	*str = "some_string"
}
