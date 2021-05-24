import React, { Fragment, useEffect, useState } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import useRouter from '../hooks/useRouter';
import PmLogo from '../PressmodoLogo';
import has from 'lodash.has'

import axios from 'axios';

import {
	EuiPage,
	EuiPageBody,
	EuiPageContent,
	EuiPageContentBody,
	EuiEmptyPrompt,
	EuiButton,
	EuiCallOut,
	EuiButtonEmpty,
	EuiLoadingSpinner,
	EuiBasicTable,
	EuiBadge,
} from '@elastic/eui';

export default () => {

	const router = useRouter();

	const [ isVerifying, setIsVerifying ] = useState(true)
	const [ processingError, setProcessingError ] = useState( { hasError: false, message: null } )
	const [ installError, setInstallError ] = useState( { hasError: false, message: null } )
	const [ requiresInstall, setRequiresInstall ] = useState( true )
	const [ requiredPlugins, setRequiredPlugins ] = useState( [] )
	const [ currentlyInstalling, setCurrentlyInstalling ] = useState( null )
	const [ tableLoading, setTableLoading ] = useState( false )
	const [ installedPlugins, setInstalledPlugins ] = useState( [] )

	let installed = [];

	const columns = [
		{
			field: 'Name',
			name: __( 'Required plugin' ),
		},
		{
			field: 'status',
			name: __( 'Status' ),
			render: ( item, plugin ) => {
				if ( currentlyInstalling === plugin.slug && ! installedPlugins.includes( plugin.slug ) ) {
					return <EuiLoadingSpinner size="m" />
				} else if ( installedPlugins.includes( plugin.slug ) ) {
					return <EuiBadge color="#4CAF50">{ __( 'Installed' ) }</EuiBadge>
				} else {
					return <EuiBadge>{ __( 'Not installed' ) }</EuiBadge>
				}
			},
		},
	];

	/**
	 * Verify required plugins are all installed or activated.
	 */
	const requestPluginsVerification = () => {
		setIsVerifying(true);

		let formData = new FormData()

		formData.append('nonce', pmOnboarding.verify_plugins_nonce)

		axios.post(pmOnboarding.verification_url, formData)
			.then(function (response) {
				setIsVerifying(false)
				setRequiresInstall( false )

				setTimeout(
					() => router.replace( '/onboarding/media' ),
					3000
				);
			})
			.catch(function (error) {

				setIsVerifying(false)

				console.log( 'verify' )

				if (error.response) {
					/*
					 * The request was made and the server responded with a
					 * status code that falls out of the range of 2xx
					 */
					if (has(error.response, 'data') && has(error.response.data, 'data')) {
						setRequiresInstall(true)
						setProcessingError({ hasError: true, message: error.response.data.data.error_message })
						//console.log( error.response.data.data.not_found )
						setRequiredPlugins( error.response.data.data.not_found )
					}
				} else if (error.request) {
					/*
					 * The request was made but no response was received, `error.request`
					 * is an instance of XMLHttpRequest in the browser and an instance
					 * of http.ClientRequest in Node.js
					 */
					setProcessingError({ hasError: true, message: __('The request was made but no response was received. Please contact support.') })
				} else {
					setProcessingError({ hasError: true, message: error.message })
				}
			});

	}

	/**
	 * Request installation of a plugin.
	 *
	 * @param {string} slug
	 */
	const requestPluginInstall = ( slug ) => {

		setTableLoading( true )

		let formData = new FormData()

		formData.append( 'plugin', slug )
		formData.append( 'nonce', pmOnboarding.install_plugin_nonce )

		axios.post(pmOnboarding.install_plugin_url, formData)
			.then(function (response) {
				if ( has(response, 'data') ) {
					//console.log( response.data.data.activated )
					setInstalledPlugins( [ ...installedPlugins, response.data.data.activated ] )

					setTimeout(
						() => checkForMissingPlugin(),
						3000
					);
				}
			})
			.catch(function (error) {
				console.error( error )

				setTableLoading( false )
				setRequiresInstall( true )
				setCurrentlyInstalling( null )

				if ( error.response && has(error.response, 'data') ) {
					setInstallError( { hasError: true, message: error.response.data.data.error_message } )
				} else {
					setInstallError({ hasError: true, message: error.message })
				}
			});

	}

	/**
	 * Ping the database to check for any required plugin that has not been installed.
	 * If any is found, trigger another ajax request.
	 */
	const checkForMissingPlugin = () => {

		setTableLoading( true )

		let formData = new FormData()

		formData.append('nonce', pmOnboarding.check_required_plugin_nonce )

		axios.post(pmOnboarding.check_plugin_install_url, formData)
			.then(function (response) {
				setTableLoading( false )
				setIsVerifying(false)
				setRequiresInstall( false )

				setInstallError( { hasError: false, message: null } )
				setProcessingError( { hasError: false, message: null } )

				/*
				setTimeout(
					() => router.replace( '/onboarding/media' ),
					4000
				); */
			})
			.catch(function (error) {

				setTableLoading( false )

				if ( error.response && has(error.response, 'data') && has(error.response.data, 'data') && has(error.response.data.data, 'slug') ) {
					/*
					 * Check if a plugin is found, if it is, send a request to install it.
					 */
					if ( has(error.response, 'data') && has(error.response.data, 'data') && has(error.response.data.data, 'slug')  ) {
						setCurrentlyInstalling( error.response.data.data.slug )
						requestPluginInstall( error.response.data.data.slug )
					}
				} else if ( error.response && has( error.response, 'data') && has( error.response.data, 'data') && ! has(error.response.data.data, 'slug') ) {
					setInstallError( { hasError: true, message: error.response.data.data.error_message } )
				} else {
					setInstallError({ hasError: true, message: error.message })
				}

			});
	}

	/**
	 * Trigger on page load.
	 */
	useEffect(() => {
		if ( requiredPlugins !== undefined && requiredPlugins.length === 0 ) {
			requestPluginsVerification()
		}
	}, [] )

	return (
		<EuiPage>
			<EuiPageBody component="div">
				<EuiPageContent verticalPosition="center" horizontalPosition="center">
					<EuiPageContentBody>

						<PmLogo></PmLogo>

						<EuiEmptyPrompt
							title={<h2> {__('Plugins installation')} </h2>}
							body={
								<Fragment>

									{isVerifying &&
										<div>
											<EuiCallOut title={__('Required plugins verification')}>{__('Verifying the required plugins for the selected demo. Please do not close this page.')}</EuiCallOut>
										</div>
									}

									{processingError.hasError === true && !isVerifying && ! tableLoading  &&
										<div>
											<EuiCallOut color="warning">
												<p>
													{processingError.message}
												</p>
											</EuiCallOut>
											<br />
										</div>
									}

									{ installError.hasError === true && ! tableLoading && ! isVerifying &&
										<div>
											<EuiCallOut color="danger">
												<p>
													{ installError.message }
												</p>
											</EuiCallOut>
											<br />
										</div>
									}

									{ processingError.hasError !== true && !isVerifying && ! requiresInstall &&
										<div>
											<EuiCallOut color="success">
												<p>
													{ __( 'All required plugins are installed and activated. Proceeding to next step...' ) }
												</p>
											</EuiCallOut>
											<br />
											<EuiLoadingSpinner size="xl" />
										</div>
									}

									{isVerifying &&
										<div>
											<br />
											<EuiLoadingSpinner size="xl" />
										</div>
									}

									{ tableLoading &&
										<div>
											<EuiCallOut>
												<p>
													{ __( 'Please wait, plugins installation is in progress.' ) }
												</p>
											</EuiCallOut>
											<br />
										</div>
									}

									{ requiresInstall && requiredPlugins !== undefined && requiredPlugins.length > 0 &&
										<EuiBasicTable
											items={requiredPlugins}
											columns={columns}
											loading={ tableLoading }
										/>
									}
								</Fragment>
							}
							actions={
								[
									<EuiButton color="primary" fill isDisabled={! requiresInstall || isVerifying } isLoading={ tableLoading } onClick={ (e) => checkForMissingPlugin() }>
										{__('Install all plugins')}
									</EuiButton>,
									<EuiButtonEmpty color="danger" isDisabled={ isVerifying || tableLoading || ! requiresInstall } onClick={(e) => router.replace('/onboarding/upload')} >{__('Go back')}</EuiButtonEmpty>
								]
							}
						/>

					</EuiPageContentBody>
				</EuiPageContent>
			</EuiPageBody>

		</EuiPage>
	)
}
