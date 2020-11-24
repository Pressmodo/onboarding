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
	EuiProgress,
} from '@elastic/eui';

export default () => {

	const router = useRouter();

	const [ isProcessing, setIsProcessing ] = useState(false)
	const [ searchReplaceStatus, setSearchReplaceStatus ] = useState( null )
	const [ searchReplaceProgress, setSearchReplaceProgress ] = useState( 0 )

	const [ successMessage, setSuccessMessage ] = useState(null)
	const [ infoMessage, setInfoMessage ] = useState(null)
	const [ errorMessage, setErrorMessage ] = useState(null)

	/**
	 * Import the database, then start search and replace
	 */
	const importDatabase = () => {

		setIsProcessing( true )
		setErrorMessage( false )

		let formData = new FormData()

		formData.append('nonce', pmOnboarding.install_db_nonce)

		axios.post(pmOnboarding.install_db_url, formData)
			.then(function (response) {
				setSuccessMessage( __( 'Database successfully imported. Now updating urls...' ) )
				setInfoMessage(null)
				setTimeout(
					() => searchReplaceStep(0,0),
					3000
				);
			})
			.catch(function (error) {
				setIsProcessing(false)
				setSuccessMessage(null)
				setInfoMessage(null)
				if (error.response && has(error.response, 'data') && has(error.response.data, 'error_message') ) {
					setErrorMessage( error.response.data.error_message )
				} else {
					setErrorMessage( error.message )
				}
			});
	}

	/**
	 * Process search and replace step.
	 *
	 * @param {string} step
	 * @param {string} page
	 * @param {array} data
	 */
	const searchReplaceStep = ( step, page, data = null ) => {

		setIsProcessing( true )
		setErrorMessage( false )

		let formData = new FormData()

		formData.append( 'nonce', pmOnboarding.search_replace_nonce )
		formData.append( 'bsr_step', step )
		formData.append( 'bsr_page', page )
		formData.append( 'bsr_data', data )

		axios.post(pmOnboarding.search_replace_url, formData)
			.then(function (response) {

				if (response && has( response, 'data') ) {
					if ( response.data.data.step === 'done' ) {

						setSuccessMessage( false )
						setIsProcessing( false )
						setSearchReplaceProgress( 100 )
						setInfoMessage( false )

					} else {

						setInfoMessage( response.data.data.message )
						setSearchReplaceProgress( response.data.data.percentage )

						setTimeout(
							() => searchReplaceStep( response.data.data.step, response.data.data.page, response.data.data.bsr_data ),
							1500
						);
					}
				} else {
					setIsProcessing( false )
					setSearchReplaceStatus( false )
					setSearchReplaceProgress( 0 )
					setSuccessMessage(null)
					setInfoMessage(null)
					console.log( response )
				}

			})
			.catch(function (error) {
				setIsProcessing( false )
				setSearchReplaceStatus( false )
				setSearchReplaceProgress( 0 )
				setSuccessMessage(null)
				setInfoMessage(null)
				console.error( error.response )
			});

	}

	return (
		<EuiPage>
			<EuiPageBody component="div">
				<EuiPageContent verticalPosition="center" horizontalPosition="center">
					<EuiPageContentBody>

						<PmLogo></PmLogo>

						<EuiEmptyPrompt
							title={<h2> { __( 'Import demo database' ) } </h2>}
							body={
								<Fragment>
									<p>
										{ __( 'The database import process will replace your site content with demo content. It is recommended you make a backup before proceeding.' ) }
									</p>

									{ successMessage && ! errorMessage &&
										<div>
											<EuiCallOut color="success">
												<p>
													{ successMessage }
												</p>
											</EuiCallOut>
										</div>
									}

									{ infoMessage && successMessage &&
										<br/>
									}

									{ infoMessage && ! errorMessage &&
										<div>
											<EuiCallOut>
												<p>
													{ infoMessage }
												</p>
											</EuiCallOut>
										</div>
									}

									{ errorMessage && ! isProcessing && ! successMessage &&
										<div>
											<br />
											<EuiCallOut color="danger">
												<p>
													{ errorMessage }
												</p>
											</EuiCallOut>
										</div>
									}

									{ searchReplaceProgress > 0 && searchReplaceProgress < 100 &&
										<div>
											<br/>
											<EuiProgress value={ searchReplaceProgress } max={100} size="l" />
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
								<EuiButton color="primary" fill onClick={ (e) => importDatabase() } isLoading={ isProcessing }>
									{ __('Import database') }
								</EuiButton>
							}
						/>
					</EuiPageContentBody>
				</EuiPageContent>
			</EuiPageBody>

		</EuiPage>
	)
}
