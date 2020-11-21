import React, { Fragment, useState } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import useRouter from '../hooks/useRouter';
import PmLogo from '../PressmodoLogo';

import axios from 'axios';
import has from 'lodash.has'

import {
	EuiPage,
	EuiPageBody,
	EuiPageContent,
	EuiPageContentBody,
	EuiEmptyPrompt,
	EuiButton,
	EuiCallOut,
	EuiLoadingSpinner,
} from '@elastic/eui';

export default () => {

	const router = useRouter();

	const [ isProcessing, setIsProcessing ] = useState(false)
	const [ errorMessage, setErrorMessage ] = useState(null)
	const [ isSuccess, setIsSuccess ] = useState(false)

	const moveMediaFiles = (files) => {

		setIsProcessing( true )
		setErrorMessage( false )

		let formData = new FormData()

		formData.append('nonce', pmOnboarding.move_media_nonce)

		axios.post(pmOnboarding.move_media_url, formData)
			.then(function (response) {
				setIsSuccess(true)

				setTimeout(
					() => {
						setIsProcessing( false )
					},
					3000
				);
			})
			.catch(function (error) {

				setIsProcessing( false )
				setIsSuccess(false)

				if (error.response && has(error.response, 'data') && has(error.response.data, 'error_message') ) {
					setErrorMessage( error.response.data.error_message )
				} else {
					setErrorMessage( error.message )
				}

			});
	}

	return (
		<EuiPage>
			<EuiPageBody component="div">
				<EuiPageContent verticalPosition="center" horizontalPosition="center">
					<EuiPageContentBody>

						<PmLogo></PmLogo>

						<EuiEmptyPrompt
							title={<h2> { __( 'Import media files' ) } </h2>}
							body={
								<Fragment>
									<p>
										{ __( 'The media import process overwrites the content of your wp-content/uploads folder. It is recommended you make a backup before proceeding.' ) }
									</p>

									{ ! isSuccess &&
										<EuiCallOut color="warning">
											<p>
												{__('Due to copyright restrictions, demo images will be replaced by placeholder images.')}
											</p>
										</EuiCallOut>
									}

									{ isSuccess &&
										<EuiCallOut color="success">
											<p>
												{__('Media images successfully imported. Proceeding to next step...')}
											</p>
										</EuiCallOut>
									}

									{ errorMessage && ! isProcessing &&
										<div>
											<br />
											<EuiCallOut color="danger">
												<p>
													{ errorMessage }
												</p>
											</EuiCallOut>
										</div>
									}

									{ isProcessing &&
										<div>
											<br />
											<EuiLoadingSpinner size="xl" />
										</div>
									}
								</Fragment>
							}
							actions={
								<EuiButton color="primary" fill onClick={ (e) => moveMediaFiles() } >
									{ __('Import media files') }
								</EuiButton>
							}
						/>
					</EuiPageContentBody>
				</EuiPageContent>
			</EuiPageBody>

		</EuiPage>
	)
}
