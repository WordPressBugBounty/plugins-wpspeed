<?php


namespace WPSpeed\Platform;

use WPSpeed\Core\Interfaces\FileSystem as FileSystemInterface;

class FileSystem implements FileSystemInterface
{

	/**
	 * @param   string  $path
	 *
	 * @return bool
	 * @throws \WPSpeed\Core\Exception
	 */
	public static function deleteFolder( $path )
	{
		$wp_filesystem = Cache::getWpFileSystem();

		return $wp_filesystem->delete( $path, true, 'd' );
	}

	/**
	 *
	 * @param   string  $file
	 * @param   string  $contents
	 *
	 * @return bool
	 * @throws \WPSpeed\Core\Exception
	 * @throws \Exception
	 */
	public static function write( $file, $contents )
	{
		//Make sure parent folder exists
		if ( ! file_exists( dirname( $file ) ) )
		{
			if ( ! self::createFolder( dirname( $file ) ) )
			{
				return false;
			}
		}

		$wp_filesystem = Cache::getWpFileSystem();

		return $wp_filesystem->put_contents( $file, $contents );
	}

	/**
	 *
	 * @param   string  $path
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function createFolder( $path )
	{
		//Create all necessary parent folders
		if ( ! file_exists( dirname( $path ) ) )
		{
			if ( ! self::createFolder( dirname( $path ) ) )
			{
				return false;
			}
		}

		$wp_filesystem = Cache::getWpFileSystem();

		return $wp_filesystem->mkdir( $path );
	}

	/**
	 *
	 * @param   string  $path
	 * @param   string  $filter
	 * @param   bool    $recurse
	 * @param   array   $exclude
	 *
	 * @return array
	 * @throws \WPSpeed\Core\Exception
	 */
	public static function lsFiles( $path, $filter = '.', $recurse = false, $exclude = array() )
	{
		$wp_filesystem = Cache::getWpFileSystem();

		$items = $wp_filesystem->dirlist( $path, false, $recurse );

		$files = array();

		if ( ! empty( $items ) )
		{
			self::filterItems( $path, $filter, $items, $files );
		}

		return $files;
	}

	/**
	 *
	 * @param   string  $path
	 * @param   string  $filter
	 * @param   array   $items
	 * @param   array   $files
	 */
	protected static function filterItems( $path, $filter, $items, &$files )
	{
		foreach ( $items as $item )
		{

			if ( $item['type'] == 'f' && preg_match( '#' . $filter . '#', $item['name'] ) )
			{
				$files[] = $path . '/' . $item['name'];
			}

			if ( $item['type'] == 'd' && ! empty( $item['files'] ) )
			{
				self::filterItems( $path . '/' . $item['name'], $filter, $item['files'], $files );
			}
		}

	}

	public static function deleteFile( $path )
	{
		$wp_filesystem = Cache::getWpFileSystem();

		return $wp_filesystem->delete( $path, false, 'f' );
	}
}