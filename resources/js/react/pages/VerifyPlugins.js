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
	const [ requiresInstall, setRequiresInstall ] = useState( true )
	const [ requiredPlugins, setRequiredPlugins ] = useState( [] )

	const columns = [
		{
			field: 'Name',
			name: __( 'Required plugin' ),
		},
		{
			field: 'status',
			name: __( 'Status' ),
			render: () => (
				<EuiBadge>{ __( 'Not installed' ) }</EuiBadge>
			),
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

				if (error.response) {
					/*
					 * The request was made and the server responded with a
					 * status code that falls out of the range of 2xx
					 */
					if (has(error.response, 'data') && has(error.response.data, 'data')) {
						setRequiresInstall(true)
						setProcessingError({ hasError: true, message: error.response.data.data.error_message })
						console.log( error.response.data.data.not_found )
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
	 * Trigger on page load.
	 */
	useEffect(() => {
		if ( requiredPlugins.length === 0 ) {
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

									{processingError.hasError === true && !isVerifying &&
										<div>
											<EuiCallOut color="warning">
												<p>
													{processingError.message}
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

									{ requiresInstall &&
										<EuiBasicTable
											items={requiredPlugins}
											columns={columns}
										/>
									}
								</Fragment>
							}
							actions={
								[
									<EuiButton color="primary" fill isDisabled={!requiresInstall}>
										{__('Install all plugins')}
									</EuiButton>,
									<EuiButtonEmpty color="danger" isDisabled={isVerifying} onClick={(e) => router.replace('/onboarding/upload')} >{__('Go back')}</EuiButtonEmpty>
								]
							}
						/>

					</EuiPageContentBody>
				</EuiPageContent>
			</EuiPageBody>

		</EuiPage>
	)
}
