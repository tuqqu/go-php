package main

func main() {
    i := 1

    println(i)

    zeroval(i)

    println(i)

    zeroptr(&i)

    println(i)

    *&i = 6

    var pi *int = &i
    println(*pi == i, *pi, *&*&*&i)

    var array = [...]int{1,2,3}

    plusTen(&array[1])

    println(array)

    var d *int = &array[2]
    var dptr **int = &d;
    var dptrptr ***int = &dptr

    plusTen(**dptrptr)

    println(array)
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
