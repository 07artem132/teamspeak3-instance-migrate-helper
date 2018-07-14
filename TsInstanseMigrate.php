<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 14.07.2018
 * Time: 18:55
 */

require __DIR__ . '/vendor/autoload.php';

set_time_limit( 90000 );
ini_set( 'memory_limit', '2048M' );
exec( 'ulimit -n 40096' );

$srcIp        = '';
$srcQueryPort = 10011;
$srcLogin     = 'serveradmin';
$srcPassword  = '';

$dstIp        = '';
$dstQueryPort = 10011;
$dstLogin     = 'serveradmin';
$dstPassword  = '';

$iconTransfer = false;
$fileTransfer = false;
$slowMode     = false;
$restoreOnly  = false;
$backaupOnly  = false;

$tempDir = sys_get_temp_dir() . '/TsInstanseMigrate';

foreach ( $argv as $item ) {
	if ( strcasecmp( $item, $_SERVER['PHP_SELF'] ) === 0 ) {
		continue;
	}
	$command = explode( '=', $item );
	if ( count( $command ) === 2 ) {
		list( $key, $value ) = $command;
	} else {
		$key = $command[0];
	}

	switch ( $key ) {
		case '--dstIp':
			$dstIp = $value;
			break;
		case '--dstQueryPort':
			$dstQueryPort = $value;
			break;
		case '--dstLogin':
			$dstLogin = $value;
			break;
		case '--dstPassword':
			$dstPassword = $value;
			break;
		case '--srcIp':
			$srcIp = $value;
			break;
		case '--srcQueryPort':
			$srcQueryPort = $value;
			break;
		case '--srcLogin':
			$srcLogin = $value;
			break;
		case '--srcPassword':
			$srcPassword = $value;
			break;
		case '--icon':
			$iconTransfer = true;
			break;
		case '--file':
			$fileTransfer = true;
			break;
		case '--slow':
			$slowMode = true;
			break;
		case '--backaup-only':
			$backaupOnly = true;
			break;
		case '--restore-only':
			$restoreOnly = true;
			break;
		case '--temp-path':
			$tempDir = $value;
			break;
		case '--help';
			echo 'Допустимые параметры:' . PHP_EOL;
			echo '--dstIp=127.0.0.1       (не обязательно если передан параметр --backaup-only)' . PHP_EOL;
			echo '--dstQueryPort=10011    (не обязательно, по умолчанию "10011")' . PHP_EOL;
			echo '--dstLogin=serveradmin  (не обязательно, по умолчанию "serveradmin")' . PHP_EOL;
			echo '--dstPassword=1111111   (не обязательно если передан параметр --backaup-only)' . PHP_EOL;
			echo '--srcIp=127.0.0.2       (не обязательно если передан параметр --restore-only)' . PHP_EOL;
			echo '--srcQueryPort=55000    (не обязательно, по умолчанию "10011")' . PHP_EOL;
			echo '--srcLogin=serveradmin  (не обязательно, по умолчанию "serveradmin")' . PHP_EOL;
			echo '--srcPassword=22222     (не обязательно если передан параметр --restore-only)' . PHP_EOL;
			echo '--icon (не обязательный параметр при его использовании бекапятся и/или разворачиваются иконки)' . PHP_EOL;
			echo '--file (не обязательный параметр при его использовании бекапятся и/или разворачиваются файлы, крайне не рекомендуется его использовать(не реализовано))' . PHP_EOL;
			echo '--slow (не обязательный параметр при его использовании после каждой команды задержка в 1 секунду)' . PHP_EOL;
			echo '--backaup-only (не обязательный параметр, используется только для создания бекапа инстанса)' . PHP_EOL;
			echo '--restore-only (не обязательный параметр, используется только для разворачивания бекапа инстанса)' . PHP_EOL;
			echo '--temp-path (не обязательный параметр, используется только для указания директории куда сохранять/откуда брать файлы бекапов)' . PHP_EOL;
			echo '--help' . PHP_EOL;
			break;
		default:
			echo 'Неизвестный параметр "' . $key . '""' . PHP_EOL;
			echo 'Допустимые параметры:' . PHP_EOL;
			echo '--dstIp=127.0.0.1       (не обязательно если передан параметр --backaup-only)' . PHP_EOL;
			echo '--dstQueryPort=10011    (не обязательно, по умолчанию "10011")' . PHP_EOL;
			echo '--dstLogin=serveradmin  (не обязательно, по умолчанию "serveradmin")' . PHP_EOL;
			echo '--dstPassword=1111111   (не обязательно если передан параметр --backaup-only)' . PHP_EOL;
			echo '--srcIp=127.0.0.2       (не обязательно если передан параметр --restore-only)' . PHP_EOL;
			echo '--srcQueryPort=55000    (не обязательно, по умолчанию "10011")' . PHP_EOL;
			echo '--srcLogin=serveradmin  (не обязательно, по умолчанию "serveradmin")' . PHP_EOL;
			echo '--srcPassword=22222     (не обязательно если передан параметр --restore-only)' . PHP_EOL;
			echo '--icon (не обязательный параметр при его использовании бекапятся и/или разворачиваются иконки)' . PHP_EOL;
			echo '--file (не обязательный параметр при его использовании бекапятся и/или разворачиваются файлы, крайне не рекомендуется его использовать(не реализовано))' . PHP_EOL;
			echo '--slow (не обязательный параметр при его использовании после каждой команды задержка в 1 секунду)' . PHP_EOL;
			echo '--backaup-only (не обязательный параметр, используется только для создания бекапа инстанса)' . PHP_EOL;
			echo '--restore-only (не обязательный параметр, используется только для разворачивания бекапа инстанса)' . PHP_EOL;
			echo '--temp-path (не обязательный параметр, используется только для указания директории куда сохранять/откуда брать файлы бекапов)' . PHP_EOL;
			echo '--help' . PHP_EOL;
			die();
			break;
	}
}

if ( $srcIp === '' && ! $restoreOnly ) {
	echo 'Для работы данного скрипта обязательным параметром является "--srcIp=" для подробностей запустите скрипт с параметром "--help"' . PHP_EOL;
	die();
}
if ( $srcPassword === '' && ! $restoreOnly ) {
	echo 'Для работы данного скрипта обязательным параметром является "--srcPassword=" для подробностей запустите скрипт с параметром "--help"' . PHP_EOL;
	die();
}
if ( $dstIp === '' && ! $backaupOnly ) {
	echo 'Для работы данного скрипта обязательным параметром является "--dstIp=" для подробностей запустите скрипт с параметром "--help"' . PHP_EOL;
	die();
}
if ( $dstPassword === '' && ! $backaupOnly ) {
	echo 'Для работы данного скрипта обязательным параметром является "--dstPassword=" для подробностей запустите скрипт с параметром "--help"' . PHP_EOL;
	die();
}

if ( ! $restoreOnly ) {
	if ( file_exists( $tempDir ) ) {
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $tempDir ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $path ) {
			if ( strcasecmp( $path->getFilename(), '.' ) === 0 || strcasecmp( $path->getFilename(), '..' ) === 0 ) {
				continue;
			}

			if ( $path->isDir() ) {
				rmdir( (string) $path );
			} else {
				unlink( (string) $path );
			}
		}
		rmdir( $tempDir );
	}
	mkdir( $tempDir );

	print( '-------------------------------' . PHP_EOL );
	print( 'Создание бекапов' . PHP_EOL );
	print( '-------------------------------' . PHP_EOL );
	$ts3_ServerInstance = TeamSpeak3::factory( "serverquery://$srcLogin:$srcPassword@$srcIp:$srcQueryPort/?#use_offline_as_virtual&blocking=0" );
	foreach ( $ts3_ServerInstance as $ts3_VirtualServer ) {
		print( 'Виртуальный сервер с портом: ' . $ts3_VirtualServer['virtualserver_port'] . PHP_EOL );
		$virtualServerPath = $tempDir . '/' . $ts3_VirtualServer['virtualserver_port'];
		mkdir( $virtualServerPath );
		file_put_contents( $virtualServerPath . '/snapshot', $ts3_VirtualServer->snapshotCreate() );
		if ( $slowMode === true ) {
			sleep( 1 );
		}
		print ( 'Файл: snapshot сохранен' . PHP_EOL );
		if ( $iconTransfer ) {
			try {
				foreach ( $ts3_VirtualServer->channelFileList( 0, 0, "/icons" ) as $key => $value ) {
					if ( $value['size'] === 0 ) {
						if ( $slowMode === true ) {
							sleep( 1 );
						}
						continue;
					}

					$download = $ts3_VirtualServer->transferInitDownload( rand( 0x0000, 0xFFFF ), 0, (string) $value['src'] );
					$transfer = TeamSpeak3::factory( "filetransfer://" . ( strstr( $download["host"], ":" ) !== false ? "[" . $download["host"] . "]" : $download["host"] ) . ":" . $download["port"] );
					$Image    = $transfer->download( $download["ftkey"], $download["size"] );
					file_put_contents( $virtualServerPath . '/' . (string) $value['name'] . image_type_to_extension( getimagesizefromstring( $Image )['2'] ), $Image );
					if ( $slowMode === true ) {
						sleep( 1 );
					}
					print ( 'Файл: ' . (string) $value['name'] . ' сохранен' . PHP_EOL );
				}
			} catch ( \Exception $e ) {
				print( 'Во время скачивания иконок произошла ошибка: ' . $e->getMessage() . PHP_EOL );
			}
		}

		if ( $slowMode === true ) {
			sleep( 1 );
		}

	}

}

print( '-------------------------------' . PHP_EOL );
print( 'Загрузка бекапов на целевой сервер' . PHP_EOL );
print( '-------------------------------' . PHP_EOL );

$ts3_ServerInstance = TeamSpeak3::factory( "serverquery://$dstLogin:$dstPassword@$dstIp:$dstQueryPort/?#use_offline_as_virtual&blocking=0" );

foreach ( scandir( $tempDir ) as $dirName ) {
	if ( is_dir( $tempDir . '/' . $dirName ) && $dirName != '.' && $dirName != '..' ) {
		print ( 'Виртуальный сервер с портом: ' . $dirName . PHP_EOL );

		$new_sid = $ts3_ServerInstance->serverCreate( array(
			"virtualserver_maxclients" => 5,
			"virtualserver_port"       => (int) $dirName,
		) );
		$sid     = (int) $new_sid['sid'];
		print ( 'Сервер создан' . PHP_EOL );
		usleep( 100000 );

		$snapshot = file_get_contents( $tempDir . '/' . $dirName . '/snapshot' );

		$ts3_ServerInstance->serverGetById( $sid )->snapshotDeploy($snapshot);
		$ts3_ServerInstance->serverListReset();
		$ts3_ServerInstance->whoamiReset();
		print ( 'Снапшот развернут' . PHP_EOL );

		if ( $slowMode === true ) {
			sleep( 1 );
		}

		if ( $iconTransfer ) {
			$virtualServerFiles = scandir( $tempDir . '/' . $dirName );
			$ts3_VirtualServer  = $ts3_ServerInstance->serverGetByPort( (int) $dirName );
			foreach ( $virtualServerFiles as $item ) {
				if ( strcasecmp( $item, '.' ) === 0 || strcasecmp( $item, '..' ) === 0 || strcasecmp( $item, 'snapshot' ) === 0 ) {
					continue;
				}
				try {
					$icon     = file_get_contents( $tempDir . '/' . $dirName . '/' . $item );
					$crc      = crc32( $icon );
					$size     = strlen( $icon );
					$upload   = $ts3_VirtualServer->transferInitUpload( rand( 0x0000, 0xFFFF ), 0, "/icon_" . $crc, $size );
					$transfer = TeamSpeak3::factory( "filetransfer://" . ( strstr( $upload["host"], ":" ) !== false ? "[" . $upload["host"] . "]" : $upload["host"] ) . ":" . $upload["port"] );
					$transfer->upload( $upload["ftkey"], $upload["seekpos"], $icon );
					print ( 'Файл: ' . $tempDir . '/' . $dirName . '/' . $item . ' загужен' . PHP_EOL );
					usleep( 100000 );
					if ( $slowMode === true ) {
						sleep( 1 );
					}
				} catch ( \Exception $e ) {
					print( 'Во время загрузки иконок произошла ошибка: ' . $e->getMessage() . PHP_EOL );
				}

			}

		}
	}
}
