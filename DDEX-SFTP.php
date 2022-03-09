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
    SFTP uploader
*/

class DDEXSFTP
{
	public $ern;
	public $uploadSettings;
    protected $ssh;

	public function getDirectory ()
	{
		return $this->ern->release->releaseICPN . "_" . date ("YmdHis");
	}

    protected function initializeSFTP ()
    {
        $this->ssh = ssh2_connect ($this->uploaderSettings->serverIp, $this->uploaderSettings->port);
        if (!$this->ssh)
            throw new ErrorException ("Couldn't connect to SSH.");

        if (!ssh2_auth_password ($this->ssh, $this->uploaderSettings->login, $this->uploaderSettings->password))
            throw new ErrorException ("Authentication failed: check credentials and retry.");

        $this->sftp = ssh2_sftp ($this->ssh);
        if (!$this->sftp)
            throw new ErrorException ("SFTP can't be initialized.");
    }

    protected function uploadFile ($local_file, $remote_file)
    {
        $sftp = $this->sftp;
        $stream = @fopen ("ssh2.sftp://$sftp/$remote_file", 'w');

        if (!$stream)
            throw new Exception ("Could not open file: $remote_file");

        $data_to_send = file_get_contents ($local_file);

        if ($data_to_send === false)
            throw new Exception ("Could not open local file: $local_file.");

        if (fwrite ($stream, $data_to_send) === false)
            throw new Exception ("Could not send data from file: $local_file.");

        fclose ($stream);
    }

    protected function mkdir ($dir)
    {
        ssh2_sftp_mkdir ($this->sftp, $dir);
    }

	public function uploadData ()
	{
        $batchDirectory = $this->getDirectory ();

		$ernName = $this->ern->release->releaseICPN . '.xml';
		$tName = hash ('sha512', microtime () . random_bytes (128)) . '_' . $this->ern->release->releaseICPN . '.xml';
		$batchTName = hash ('sha512', microtime () . random_bytes (128)) . '_' . $this->ern->release->releaseICPN . '.batchSignal';

        $this->initializeSFTP ();

        $pathBatch = '/' . $batchDirectory;
        $pathResources = $pathBatch . '/resources';

        $this->mkdir ($pathBatch);

        if ($this->ern->release->releaseNoData != true)
		{
			$this->mkdir ($pathResources);

			foreach ($this->ern->release->releaseTracks as $trackData)
			{
				$this->uploadFile ($trackData->actualFileName, $pathResources . '/' . $trackData->fileName);
			}

			$this->uploadFile ($this->ern->release->releaseCoverArt->actualFilePath, $pathResources . '/' . $this->ern->release->releaseCoverArt->filename);
		}

        file_put_contents ($tName, $this->ern->gen ());

		$this->uploadFile ($tName, $pathBatch . '/' . $ernName);

		if ($this->uploadSettings->confirmationFile instanceof ConfirmationFile)
		{
			file_put_contents ($batchTName, $this->uploadSettings->confirmationFile->fileContents);

		    $this->uploadFile ($batchTName, $pathBatch . '/' . $this->uploadSettings->confirmationFile->fileName);

			unlink ($batchTName);
		}
	}

}
