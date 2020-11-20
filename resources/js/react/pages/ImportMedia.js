import React, { Fragment } from 'react';
import { sprintf, __ } from '@wordpress/i18n';
import useRouter from '../hooks/useRouter';
import PmLogo from '../PressmodoLogo';
import has from 'lodash.has'

import {
	EuiPage,
	EuiPageBody,
	EuiPageContent,
	EuiPageContentBody,
	EuiEmptyPrompt,
	EuiButton,
	EuiCallOut,
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
							title={<h2> { __( 'Import media files' ) } </h2>}
							body={
								<Fragment>
									<p>
										{ __( 'The media import process overwrites the content of your wp-content/uploads folder. It is recommended you make a backup before proceeding.' ) }
									</p>
									<EuiCallOut color="warning">
										<p>
											{__('Due to copyright restrictions, demo images will be replaced by placeholder images.')}
										</p>
									</EuiCallOut>
								</Fragment>
							}
							actions={
								<EuiButton color="primary" fill>
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
