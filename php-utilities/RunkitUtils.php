<?php
/**
 * Runkitを使うのに便利なユーティリティメソッド郡
 * 基本的にクロージャを使った遅延評価を多用して、runkitの面倒な関数を書かないようにさせる
 *
 * Created by IntelliJ IDEA.
 * User: mk2
 * Date: 2014/12/04
 * Time: 12:36
 */

/**
 * Class NoRunkitException
 *
 * Runkitが無いことを知らせるエクセプション
 */
class NoRunkitException extends RuntimeException {

}

// Runkitがない状況でもエラーが発生しないように、定数を置き換えておく
if ( defined ( 'RUNKIT_ACC_STATIC' ) ) {
    define( '_RUNKIT_ACC_STATIC_', RUNKIT_ACC_STATIC );
} else {
    define( '_RUNKIT_ACC_STATIC_', '' );
}

if ( defined ( 'RUNKIT_ACC_PUBLIC' ) ) {
    define( '_RUNKIT_ACC_PUBLIC_', RUNKIT_ACC_PUBLIC );
} else {
    define( '_RUNKIT_ACC_PUBLIC_', '' );
}

if ( defined ( 'RUNKIT_ACC_PROTECTED' ) ) {
    define( '_RUNKIT_ACC_PROTECTED_', RUNKIT_ACC_PROTECTED );
} else {
    define( '_RUNKIT_ACC_PROTECTED_', '' );
}

if ( defined ( 'RUNKIT_ACC_PRIVATE' ) ) {
    define( '_RUNKIT_ACC_PRIVATE_', RUNKIT_ACC_PRIVATE );
} else {
    define( '_RUNKIT_ACC_PRIVATE_', '' );
}

/**
 * Interface RunkitUtilsConstants
 *
 * RunkitUtilsで使う定数をまとめたインターフェース
 */
interface RunkitUtilsConstants {

    // 退避しておくメソッドに付くプレフィックス
    const RUNKIT_PREFIX = '_runkit_';

    // 退避しておくメソッドの名前を難読化するかどうか
    const RUNKIT_PREFIX_OBFUSCATE_ON  = true;
    const RUNKIT_PREFIX_OBFUSCATE_OFF = false;

    const RUNKIT_ACC_STATIC    = _RUNKIT_ACC_STATIC_;
    const RUNKIT_ACC_PUBLIC    = _RUNKIT_ACC_PUBLIC_;
    const RUNKIT_ACC_PROTECTED = _RUNKIT_ACC_PROTECTED_;
    const RUNKIT_ACC_PRIVATE   = _RUNKIT_ACC_PRIVATE_;

}

/**
 * Class RunkitUtils
 *
 * RunkitUtilsトレイト
 */
trait RunkitUtils {

    /**
     * runkitの処理を自動で挟むためのメソッド
     * 定数用(クラス定数も書き換え可能)
     *
     * @param callable $wrapClosure       この中でrunkitで書き換えられたメソッドを呼び出す
     * @param array    $constantInfoArray 書き換える定数についての情報をまとめた配列。存在しない定数を指定すると作成される
     *                                    サンプルデータ:
     *                                    [['HogeClass::CONST_VALUE', 'hunyahunya']]
     *                                    [['CONST_VALUE', 'hunyahunya']]
     *
     * @throws Exception
     */
    protected static function runkitConstantMocking ( callable $wrapClosure, array $constantInfoArray ) {

        if ( self::isRunkitAvailable () === true && empty( $constantInfoArray ) === false ) {

            $needTurnBack = [ ];

            foreach ( $constantInfoArray as $constantInfo ) {
                list( $constantName, $constantValue ) = $constantInfo;
                if ( defined ( $constantName ) ) {
                    $needTurnBack[ $constantName ] = constant ( $constantName );
                    runkit_constant_remove ( $constantName );
                }
                runkit_constant_add ( $constantName, $constantValue );
            }

            $turnBack = function () use ( $constantInfoArray, $needTurnBack ) {

                foreach ( $constantInfoArray as $constantInfo ) {
                    list( $constantName, ) = $constantInfo;
                    runkit_constant_remove ( $constantName );
                    if ( isset( $needTurnBack[ $constantName ] ) ) {
                        runkit_constant_add ( $constantName, $needTurnBack[ $constantName ] );
                    }
                }
            };

            try {
                call_user_func ( $wrapClosure );
                goto finish_runkit_constant_mocking;
            } catch ( Exception $e ) {
                $turnBack();
                throw $e;
            }

            finish_runkit_constant_mocking:
            $turnBack();
        }
    }

    /**
     * runkitの処理を自動で挟むためのメソッド
     * メソッド用
     *
     * @param callable $wrapClosure     この中でrunkitで書き換えられたメソッドを呼び出す
     * @param array    $methodInfoArray 書き換えるメソッドについての情報をまとめた配列。存在しないメソッドを指定すると作成される
     *                                  サンプルデータ:
     *                                  [['HogeClass', 'hogeMethod', '$arg0, $arg1', 'return $arg0 . $arg1;', self::RUNKIT_ACC_PUBLIC,
     *                                  self::RUNKIT_PREFIX_OBFUSCATE_ON]]
     *
     * @throws Exception
     */
    protected static function runkitMethodMocking ( callable $wrapClosure, array $methodInfoArray ) {

        if ( self::isRunkitAvailable () === true && empty( $methodInfoArray ) === false ) {

            $getPrefix = function ( $makeObfuscated = true ) {

                if ( $makeObfuscated ) {
                    return uniqid ( self::RUNKIT_PREFIX );
                } else {
                    return self::RUNKIT_PREFIX;
                }
            };


            $prefixArray = [ ];
            $needTurnBack = [ ];

            foreach ( $methodInfoArray as $methodInfo ) {

                if ( 6 === count ( $methodInfo ) ) {
                    list( $className, $methodName, $methodArgs, $methodBody, $flags, $makeObfuscated ) = $methodInfo;
                } else {
                    list( $className, $methodName, $methodArgs, $methodBody, $flags ) = $methodInfo;
                    $makeObfuscated = self::RUNKIT_PREFIX_OBFUSCATE_ON;
                }

                // プリフィックス作成
                $prefix = $getPrefix( $makeObfuscated );
                $prefixArray[ $className . $methodName ] = $prefix;

                if ( method_exists ( $className, $methodName ) ) {
                    runkit_method_rename ( $className, $methodName, $prefix . $methodName );
                    $needTurnBack[ $className . $methodName ] = true;
                }
                runkit_method_add ( $className, $methodName, $methodArgs, $methodBody, $flags );
            }

            $turnBack = function () use ( $prefixArray, $methodInfoArray, $needTurnBack ) {

                foreach ( $methodInfoArray as $methodInfo ) {
                    list( $className, $methodName, ) = $methodInfo;
                    $prefix = $prefixArray[ $className . $methodName ];
                    runkit_method_remove ( $className, $methodName );
                    if ( isset( $needTurnBack[ $className . $methodName ] ) && $needTurnBack[ $className . $methodName ] ) {
                        runkit_method_rename ( $className, $prefix . $methodName, $methodName );
                    }
                }
            };

            try {
                call_user_func ( $wrapClosure );
                goto finish_runkit_method_mocking;
            } catch ( Exception $e ) {
                $turnBack();
                throw $e;
            }

            finish_runkit_method_mocking:
            $turnBack();
        }
    }

    /**
     * runkitの処理を自動で挟むためのメソッド
     * 関数用
     *
     * @param callable $wrapClosure       この中でrunkitで書き換えられたメソッドを呼び出す
     * @param array    $functionInfoArray 書き換える関数の情報をまとめた配列。存在しない関数を指定すると作成される
     *                                    サンプルデータ:
     *                                    [['hogeFunction','$arg0, $arg1','return $arg0 . $arg1;']]
     *
     * @throws Exception
     */
    protected static function runkitFunctionMocking ( callable $wrapClosure, array $functionInfoArray ) {

        if ( self::isRunkitAvailable () === true && empty( $functionInfoArray ) === false ) {

            $getPrefix = function ( $makeObfuscated = true ) {

                if ( $makeObfuscated ) {
                    return uniqid ( self::RUNKIT_PREFIX );
                } else {
                    return self::RUNKIT_PREFIX;
                }
            };

            $prefixArray = [ ];
            $needTurnBack = [ ];

            foreach ( $functionInfoArray as $functionInfo ) {

                if ( 4 === count ( $functionInfo ) ) {
                    list( $functionName, $functionArgs, $functionBody, $makeObfuscated ) = $functionInfo;
                } else {
                    list( $functionName, $functionArgs, $functionBody ) = $functionInfo;
                    $makeObfuscated = self::RUNKIT_PREFIX_OBFUSCATE_ON;
                }

                // プレフィックス作成
                $prefix = $getPrefix( $makeObfuscated );
                $prefixArray[ $functionName ] = $prefix;

                if ( function_exists ( $functionName ) ) {
                    runkit_function_rename ( $functionName, $prefix . $functionName );
                    $needTurnBack[ $functionName ] = true;
                }
                runkit_function_add ( $functionName, $functionArgs, $functionBody );
            }

            $turnBack = function () use ( $prefixArray, $functionInfoArray, $needTurnBack ) {

                foreach ( $functionInfoArray as $functionInfo ) {
                    list( $functionName, ) = $functionInfo;
                    $prefix = $prefixArray[ $functionName ];
                    runkit_function_remove ( $functionName );
                    if ( isset( $needTurnBack[ $functionName ] ) && $needTurnBack[ $functionName ] ) {
                        runkit_function_rename ( $prefix . $functionName, $functionName );
                    }
                }
            };

            try {
                call_user_func ( $wrapClosure );
                goto finish_runkit_function_mocking;
            } catch ( Exception $e ) {
                $turnBack();
                throw $e;
            }

            finish_runkit_function_mocking:
            $turnBack();
        }
    }

    /**
     * runkitが利用可能かどうかでテストをスキップさせる関数
     * 利用可能 -> スキップさせない
     * 利用不可能 -> スキップさせる
     *
     * @throws NoRunkitException
     */
    protected static function skipIfRunkitUnavailable () {

        $isRunkitAvailable = self::isRunkitAvailable ();

        if ( $isRunkitAvailable === false ) {

            throw new NoRunkitException();

        }
    }

    /**
     * runkitが利用可能かどうかをチェックする関数
     *
     * @link https://github.com/zenovich/runkit
     *
     * @return bool
     */
    protected static function isRunkitAvailable () {

        $functions = [
            'runkit_method_add',
            'runkit_method_copy',
            'runkit_method_redefine',
            'runkit_method_remove',
            'runkit_method_rename',
            'runkit_function_add',
            'runkit_function_copy',
            'runkit_function_redefine',
            'runkit_function_remove',
            'runkit_function_rename',
            'runkit_constant_add',
            'runkit_constant_remove',
            'runkit_constant_redefine',
        ];

        $isAvailable = array_reduce ( $functions, function ( $accu, $var ) {

            return function_exists ( $var ) && $accu;
        }, true );

        return $isAvailable;
    }

}