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
	const [ searchReplaceProgress, setSearchReplaceProgress ] = useState( 0 )

	const [ successMessage, setSuccessMessage ] = useState(null)
	const [ infoMessage, setInfoMessage ] = useState(null)
	const [ errorMessage, setErrorMessage ] = useState(null)

	const [ installComplete, setInstallComplete ] = useState(false)

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

						updateUserAccount()

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
					setSearchReplaceProgress( 0 )
					setSuccessMessage(null)
					setInfoMessage(null)
					console.log( response )
				}

			})
			.catch(function (error) {
				setIsProcessing( false )
				setSearchReplaceProgress( 0 )
				setSuccessMessage(null)
				setInfoMessage(null)
				console.error( error.response )
			});

	}

	/**
	 * Merge the currently logged in user account within the demo database.
	 */
	const updateUserAccount = () => {

		setIsProcessing( true )
		setErrorMessage( false )
		setInfoMessage( __( 'Updating user account...' ) )

		let formData = new FormData()

		formData.append( 'nonce', pmOnboarding.update_account_nonce )

		axios.post(pmOnboarding.update_account_url, formData)
			.then(function (response) {
				setTimeout(
					() => {
						setInfoMessage( __( 'Account successfully updated.' ) )
						setIsProcessing( false )
						replaceDatabase()
					},
					1500
				);
			})
			.catch(function (error) {

				setIsProcessing( false )
				setSuccessMessage(null)
				setInfoMessage(null)

				if ( error.response && has(error.response, 'data') ) {
					setErrorMessage( error.response.data.data.error_message )
				} else {
					setErrorMessage( error.message )
				}
				console.error( error )
			});

	}

	/**
	 * Make the demo database the "real" database.
	 */
	const replaceDatabase = () => {

		setIsProcessing( true )
		setErrorMessage( false )
		setInfoMessage( __( 'Almost done, cleaning up orphan values...' ) )

		let formData = new FormData()

		formData.append( 'nonce', pmOnboarding.replace_db_nonce )

		axios.post(pmOnboarding.replace_db_url, formData)
			.then(function (response) {
				setTimeout(
					() => {
						setInfoMessage(null)
						setSuccessMessage( __( 'Demo installation successfully completed. Click the button below to log into your new website.' ) )
						setIsProcessing( false )
						setInstallComplete(true)
					},
					1500
				);
			})
			.catch(function (error) {

				setIsProcessing( false )
				setSuccessMessage(null)
				setInfoMessage(null)

				if ( error.response && has( error.response, 'data') && has( error.response.data, 'data') ) {
					setErrorMessage( error.response.data.data.error_message )
				} else {
					setErrorMessage( error.message )
				}
				console.error( error )
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

										{
											pmOnboarding.demo_installed === '1' &&
											<div>
												<EuiCallOut color="success">
													<p>
														{ __( 'It looks like you have already installed a theme demo on this website. If you wish to install a new demo please start the process from the beginning.' ) }
													</p>
												</EuiCallOut>
												<br/>
												<EuiButton color="primary" fill onClick={ (e) => router.replace( '/onboarding' ) }>
													{ __('Install a new demo') }
												</EuiButton>
											</div>
										}

										{ pmOnboarding.demo_installed !== '1' &&
											<div>

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

												{ installComplete &&
													<div>
														<br/>
														<EuiButton color="primary" fill href={ pmOnboarding.login_url }>
															{ __('Log in') }
														</EuiButton>
													</div>
												}

												{ isProcessing &&
													<div>
														<br />
														<EuiLoadingSpinner size="xl" />
													</div>
												}

											</div>
										}

									</Fragment>
								}
								actions={
									<EuiButton color="primary" fill onClick={ (e) => importDatabase() } disabled={ pmOnboarding.demo_installed === '1' || installComplete } isLoading={ isProcessing }>
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
