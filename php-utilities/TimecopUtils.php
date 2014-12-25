<?php
/**
 * Timecopを使うのに便利なユーティリティメソッド郡
 * 基本的にクロージャを使った遅延評価を多用して、timecopの面倒な関数を書かないようにさせる
 *
 * Created by IntelliJ IDEA.
 * User: mk2
 * Date: 2014/12/04
 * Time: 12:50
 */

/**
 * Class NoTimecopException
 *
 * Timecopが無いことを知らせるエクセプション
 */
class NoTimecopException extends RuntimeException {

}

/**
 * Interface TimecopUtilsConstants
 *
 * トレイトで使用する定数をまとめたインターフェース。トレイトをuseする場合は必ずimplementsする。
 */
interface TimecopUtilsConstants {

    const FREEZE_ON  = true;
    const FREEZE_OFF = false;

    const TRAVEL_ON  = true;
    const TRAVEL_OFF = false;

}

/**
 * Class TimecopUtils
 *
 * Timecop周りの便利なメソッドをまとめたトレイト
 */
trait TimecopUtils {

    /**
     * timecop_freezeの処理を自動で挟むためのメソッド
     *
     * @param callable $wrapClosure
     * @param int      $timestamp 無いと現在時刻でフリーズされる
     * @param bool     $noReturn  timecop_returnを呼ばないようにする。デフォルトはself::FREEZE_OFF
     *
     * @throws Exception
     */
    protected static function timecopFreezing ( callable $wrapClosure, $timestamp = null, $noReturn = false ) {

        $timestamp = ( $timestamp ) ?: time ();

        if ( self::isTimecopAvailable () && is_int ( $timestamp ) ) {

            timecop_freeze ( $timestamp );

            $meltBack = function () use ( $noReturn ) {

                if ( $noReturn === self::FREEZE_OFF ) {
                    timecop_return ();
                }
            };

            try {
                call_user_func ( $wrapClosure );
                goto finish_timecop_freezing;
            } catch ( Exception $e ) {
                $meltBack();
                throw $e;
            }

            finish_timecop_freezing:
            $meltBack();
        }
    }

    /**
     * timecop_travelの処理を自動で挟むためのメソッド
     *
     * @param callable $wrapClosure
     * @param int      $timestamp 無いと現在時刻にトラベルする
     * @param bool     $noReturn  timecop_returnを呼ばないようにする。デフォルトはself::TRAVEL_OFF
     *
     * @throws Exception
     */
    protected static function timecopTraveling ( callable $wrapClosure, $timestamp = null, $noReturn = false ) {

        $timestamp = ( $timestamp ) ?: time ();

        if ( self::isTimecopAvailable () && is_int ( $timestamp ) ) {

            timecop_travel ( $timestamp );

            $comeBack = function () use ( $noReturn ) {

                if ( $noReturn === self::TRAVEL_OFF ) {
                    timecop_return ();
                }
            };

            try {
                call_user_func ( $wrapClosure );
                goto finish_timecop_traveling;
            } catch ( Exception $e ) {
                $comeBack();
                throw $e;
            }

            finish_timecop_traveling:
            $comeBack();
        }
    }

    /**
     * 時間を戻す
     */
    protected static function timecopReturn () {

        if ( self::isTimecopAvailable () ) {

            timecop_return ();
        }
    }

    /**
     * php-timecopが利用可能かどうかでテストをスキップさせる関数
     * 利用可能 -> スキップさせない
     * 利用不可能 -> スキップさせる
     *
     * @throws NoTimecopException
     */
    protected static function skipIfTimecopUnavailable () {

        $isTimecopAvailable = self::isTimecopAvailable ();

        if ( $isTimecopAvailable === false ) {

            throw new NoTimecopException();

        }
    }

    /**
     * php-timecopが利用可能かどうかをチェックする関数
     *
     * @link https://github.com/hnw/php-timecop
     *
     * @return bool
     */
    protected static function isTimecopAvailable () {

        $functions = [
            'timecop_freeze',
            'timecop_return',
            'timecop_travel',
        ];

        $isAvailable = array_reduce ( $functions, function ( $accu, $var ) {

            return function_exists ( $var ) && $accu;
        }, true );

        return $isAvailable;
    }

    /**
     * タイムスタンプを返す
     *
     * @param DateTime $datetime
     *
     * @return int タイムスタンプ
     */
    protected static function getTimestampFromDateTime ( DateTime $datetime ) {

        return $datetime->getTimestamp ();
    }

    /**
     * 文字列からタイムスタンプを生成
     *
     * @param string $datestr
     *
     * @return int タイムスタンプ
     */
    protected static function getTimestampFromStr ( $datestr ) {

        return strtotime ( $datestr );
    }


}