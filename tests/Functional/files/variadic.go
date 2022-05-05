package main

func sum(nums ...int) {
    print(nums, " ")
    total := 0
    for _, num := range nums {
        total += num
    }
    println(total)
}

func main() {
    sum()
    sum(1, 2)
    sum(1, 2, 3)

    nums := []int{1, 2, 3, 4}

    sum(nums...)
    sum([]int{5,6,7}...)
}
