import React, { Fragment, useState } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import useRouter from '../hooks/useRouter';
import PmLogo from '../PressmodoLogo';

import axios from 'axios';
import qs from 'qs';
import has from 'lodash.has'

import {
	EuiPage,
	EuiPageBody,
	EuiPageContent,
	EuiEmptyPrompt,
	EuiButton,
	EuiButtonEmpty,
	EuiCallOut,
	EuiFilePicker,
} from '@elastic/eui';

export default () => {

	const router = useRouter();

	const [ isUploading, setIsUploading ] = useState(false);
	const [ canUpload, setCanUpload ] = useState(false)
	const [ processingError, setProcessingError ] = useState( { hasError: false, message: null } )
	const [ demoPackageFile, setDemoPackageFile ] = useState( null )

	const uploadDemoPackage = ( files ) => {

		setIsUploading( true )
		setProcessingError( { hasError: false, message: null } )

		let formData = new FormData()

		formData.append( 'file', files[0] )
		formData.append( 'action', 'pm_onboarding_upload' )
		formData.append( 'nonce', pmOnboarding.upload_package_nonce )

		axios.post( pmOnboarding.ajax_url, formData,
			{
				headers: {
					'Content-Type': 'multipart/form-data'
				}
			}
			)
			.then( function (response) {
				console.log(response);
				console.log( 'hehehe' );

				setIsUploading( false )
			})
			.catch(function (error) {

				if (error.response) {
					/*
					 * The request was made and the server responded with a
					 * status code that falls out of the range of 2xx
					 */
					if ( has( error.response, 'data' ) && has( error.response.data, 'data' ) ) {
						setProcessingError( { hasError: true, message: error.response.data.data.error_message } )
					}
				} else if (error.request) {
					/*
					 * The request was made but no response was received, `error.request`
					 * is an instance of XMLHttpRequest in the browser and an instance
					 * of http.ClientRequest in Node.js
					 */
					setProcessingError( { hasError: true, message: __( 'The request was made but no response was received. Please contact support.' ) } )
				} else {
					setProcessingError( { hasError: true, message: error.message } )
				}

				setIsUploading( false )
				console.log(error);

			});
	}

	return (
		<EuiPage>
			<EuiPageBody component="div">
				<EuiPageContent verticalPosition="center" horizontalPosition="center">

					<PmLogo></PmLogo>

					<EuiEmptyPrompt
						title={<h2> { __( 'Upload demo package' ) } </h2>}
						body={
							<Fragment>
								<p>
									{ __('Select the .zip package of the demo you wish to import. Please refer to the documentation of the theme for more information.') }
								</p>

								{ isUploading &&
									<div>
										<EuiCallOut
											size="m"
											title={ __( 'Uploading & verifying the demo package. Do not close this page.' ) }
										/>
										<br/>
									</div>
								}

								{ processingError.hasError === true && ! isUploading &&
									<div>
										<EuiCallOut title={ __( 'Something went wrong' ) } color="danger">
											<p>
												{ processingError.message }
											</p>
										</EuiCallOut>
										<br/>
									</div>
								}

								<EuiFilePicker
									id="demo-package-file"
									initialPromptText={ __( 'Select or drag and drop the demo package file.' ) }
									onChange={ (files) => {
										if ( files.length > 0 ) {
											setCanUpload( true )
											setDemoPackageFile( files )
										} else {
											setCanUpload( false )
											setDemoPackageFile( null )
										}
									}}
									fullWidth={ true }
									display="large"
									isLoading={ isUploading }
									aria-label={ __( 'Select or drag and drop the demo package file.' ) }
								/>
							</Fragment>
						}
						actions={
							[
								<EuiButton color="primary" fill onClick={ () => uploadDemoPackage( demoPackageFile ) } isDisabled={ ! canUpload } isLoading={ isUploading }>
									{ __('Upload demo package') }
								</EuiButton>,
								<EuiButtonEmpty color="danger" isDisabled={ isUploading } onClick={ (e) => router.replace('/onboarding') } >{ __( 'Go back' ) }</EuiButtonEmpty>
							]
						}
					/>
				</EuiPageContent>
			</EuiPageBody>

		</EuiPage>
	)
}
