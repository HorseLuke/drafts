[draft]php_aop_sample
======
一种采取数组模拟“指针移动”的php aop（Aspect Oriented Programming，面向切面）思路和demo

当前比较多的php aop思路，是对调用的方法采取反射处理，并且使用装饰模式，通过对advice的递归调用链，来完成aop的流程

此处提出一种不依赖反射和对象递归调用的方法，仅依赖数组索引，同时模拟“指针”移动来完成类似功能

当然，这种方式可能没其它aop那么优雅，但不需要对数组多次变换和归并......


该demo已经实现的aop功能有：

    - 基于before、after、around通知（Advice）的切面（Aspect）
    - 基于introduction通知的类“转换”
   
该demo不能实现的aop功能有：

    - advice延迟运行（即在advice运行过程中，代码判断当前运行时机不适合，要求放到后面再一次运行；话说spring也没这么做啊...）
    - 动态添加pointcut（只能在配置中预定义）
    - 基于注释的pointcut（只能在配置中预定义。php中实现Tokenizer的话，代价有点大吧？！有些aop支持这功能，但需要缓存支持...）
    - advice中运行advice（确实有人提出这需求，但这个比较容易搞混advice的单一职责吧？！）

该demo不建议的aop功能：

    - advice中调用lrn_aop_container容器内的方法（虽然许多情况下不能避免，但不建议调用过多，否则容易死循环...）

参考过的链接（在此致谢）
======
    - @link http://www.linuxidc.com/Linux/2012-03/56516.htm
    - @link http://pandonix.iteye.com/blog/336873
    - @link http://javacrazyer.iteye.com/blog/794035
    - @link http://cloudbbs.org/forum.php?mod=viewthread&tid=7820



语言
======
php

依赖组件
======
php >= 5.2.0 with SPL


更新日志
======
2013/8/2 16:00 更新：

初始版本
