import React, { Fragment, useState } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import useRouter from '../hooks/useRouter';
import PmLogo from '../PressmodoLogo';
import { steps } from '../utilities/steps'

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
											title={ __( 'Please wait while we upload & verify the demo package. Do not close this page.' ) }
										/>
										<br/>
									</div>
								}

								<EuiFilePicker
									id="demo-package-file"
									initialPromptText={ __( 'Select or drag and drop the demo package file.' ) }
									onChange={ (files) => {
										if ( files.length > 0 ) {
											setCanUpload( true )
										} else {
											setCanUpload( false )
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
							<EuiButton color="primary" fill onClick={ () => setIsUploading(true) } isDisabled={ ! canUpload } isLoading={ isUploading } >
								{ __('Upload demo package') }
							</EuiButton>
						}
					/>
				</EuiPageContent>
			</EuiPageBody>

		</EuiPage>
	)
}
