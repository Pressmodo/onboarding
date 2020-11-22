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

									{ isSuccess &&
										<EuiCallOut color="success">
											<p>
												{__('Demo database successfully imported. Proceeding to next step...')}
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
