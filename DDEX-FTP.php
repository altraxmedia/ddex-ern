<?php

/*
    A library to generate DDEX ERN XML files from pre-defined PHP classes

    Format: DDEX ERN 3.8.2 (Standart).

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.

    This library licensed under the MIT License.

    Copyright (c) 2022 Georgy Akhmetov, Serhii Shmaida, Saveliy Safonov, Al-Trax Media Limited
*/

/*
    FTP uploader
*/

class DDEXFTP
{
	public $ern;
	public $uploadSettings;

	public function getDirectory ()
	{
		return $ern->releaseICPN . "_" . date ("YmdHis");
	}

	public function uploadData ()
	{
		$batchDirectory = $this->getDirectory ();

		$conn_id = ftp_connect ($this->uploadSettings->serverIp, $this->uploadSettings->port);
		$login_result = ftp_login ($conn_id, $this->uploadSettings->login, $this->uploadSettings->password);
		ftp_pasv ($conn_id, $this->uploadSettings->pasvFTP);

		if ((!$conn_id) || (!$login_result))
		{
			throw new DomainException ('FTP uploader error: can\'t establish connection to this server. Please check settings and retry.');
		}

		ftp_mkdir ($conn_id, $batchDirectory);

		ftp_chdir ($conn_id, $batchDirectory);

		ftp_mkdir ($conn_id, "resources");

		ftp_chdir ($conn_id, "resources");

		foreach ($ern->releaseTracks as $trackData)
		{
			ftp_put ($conn_id, $trackData->fileName, $trackData->actualFileName, FTP_BINARY);
		}

	}

}
