<?php

/**
 * Class Brizy_Admin_Cloud_BlockUploader
 */
class Brizy_Admin_Cloud_LayoutBridge extends Brizy_Admin_Cloud_AbstractBridge {


	/**
	 * @param Brizy_Editor_Block $layout
	 *
	 * @return mixed|void
	 * @throws Exception
	 */
	public function export( $layout ) {

		// check if the assets are uploaded in cloud
		// upload them if needed
		// create the block in cloud

		$media = json_decode( $layout->getMedia() );

		if ( ! $media || ! isset( $media->fonts ) ) {
			throw new Exception( 'No fonts property in media object' );
		}

		if ( ! $media || ! isset( $media->images ) ) {
			throw new Exception( 'No images property in media object' );
		}

		$bridge = new Brizy_Admin_Cloud_MediaBridge( $this->client );
		foreach ( $media->images as $uid ) {
			$bridge->export( $uid );
		}

		$bridge = new Brizy_Admin_Cloud_FontBridge( $this->client );
		foreach ( $media->fonts as $fontUid ) {
			$bridge->export( $fontUid );
		}

		$bridge = new Brizy_Admin_Cloud_ScreenshotBridge( $this->client );
		$bridge->export( $layout );

		$layoutObject = $this->client->createOrUpdateLayout( $layout );

		$layout->setSynchronized( $this->client->getBrizyProject()->getCloudAccountId(), $layoutObject->uid );

		$layout->saveStorage();
	}

	/**
	 * @param $layoutId
	 *
	 * @return mixed|void
	 * @throws Exception
	 */
	public function import( $layoutId ) {
		global $wpdb;

		$layouts = $this->client->getLayouts( [ 'uid' => $layoutId ] );

		if ( ! isset( $layouts[0] ) ) {
			return;
		}

		try {
			$wpdb->query( 'START TRANSACTION ' );

			$layout = (array) $layouts[0];

			$name = md5( time() );
			$post = wp_insert_post( array(
				'post_title'  => $name,
				'post_name'   => $name,
				'post_status' => 'publish',
				'post_type'   => Brizy_Admin_Layouts_Main::CP_LAYOUT
			) );

			if ( $post ) {
				$brizyPost = Brizy_Editor_Layout::get( $post, $layout['uid'] );

				if ( isset( $layout['media'] ) ) {
					$brizyPost->setMedia( $layout['media'] );
				}
				if ( isset( $layout['meta'] ) ) {
					$brizyPost->setMeta( $layout['meta'] );
				}
				$brizyPost->set_editor_data( $layout['data'] );
				$brizyPost->set_uses_editor( true );
				$brizyPost->set_needs_compile( true );
				$brizyPost->saveStorage();
				$brizyPost->setDataVersion( 1 );
				$brizyPost->setSynchronized( $this->client->getBrizyProject()->getCloudAccountId(), $layout['uid'] );
				$brizyPost->save();


				// import fonts
				if ( isset( $layout['media'] ) ) {
					$blockMedia = json_decode( $layout['media'] );

					$fontBridge = new Brizy_Admin_Cloud_FontBridge( $this->client );
					if ( isset( $blockMedia->fonts ) ) {
						foreach ( $blockMedia->fonts as $cloudFontUid ) {
							$fontBridge->import( $cloudFontUid );
						}
					}

					$mediaBridge = new Brizy_Admin_Cloud_MediaBridge( $this->client );
					$mediaBridge->setBlockId( $post );
					if ( isset( $blockMedia->images ) ) {
						foreach ( $blockMedia->images as $mediaUid ) {
							$mediaBridge->import( $mediaUid );
						}
					}
				}

			}
			$wpdb->query( 'COMMIT' );
		} catch ( Exception $e ) {
			$wpdb->query( 'ROLLBACK' );
			Brizy_Logger::instance()->critical( 'Importing layout ' . $layoutId . ' failed', [ $e ] );
		}
	}

	/**
	 * @param Brizy_Editor_Block $layout
	 *
	 * @return mixed|void
	 * @throws Exception
	 */
	public function delete( $layout ) {

		if ( $layout->getCloudId() ) {
			$this->client->deleteLayout( $layout->getCloudId() );
		}
	}
}
