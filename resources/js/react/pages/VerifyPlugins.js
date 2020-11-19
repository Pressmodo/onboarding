import React, { Fragment } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import useRouter from '../hooks/useRouter';
import PmLogo from '../PressmodoLogo';

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
} from '@elastic/eui';

export default () => {

	const router = useRouter();

	return (
		<EuiPage>
			<EuiPageBody component="div">
				<EuiPageContent verticalPosition="center" horizontalPosition="center">
					<EuiPageContentBody>

						<PmLogo></PmLogo>

						<EuiEmptyPrompt
							title={<h2> { __( 'Verifying plugins' ) } </h2>}
							body={
								<Fragment>

									<p>
										{ __('Select the .zip package of the demo you wish to import. Please refer to the documentation of the theme for more information.') }
									</p>

									<EuiLoadingSpinner size="xl" />

								</Fragment>
							}
							actions={
								[
									<EuiButtonEmpty color="danger" onClick={ (e) => router.replace('/onboarding/upload') } >{ __( 'Go back' ) }</EuiButtonEmpty>
								]
							}
						/>

					</EuiPageContentBody>
				</EuiPageContent>
			</EuiPageBody>

		</EuiPage>
	)
}
